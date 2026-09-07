jQuery(document).ready(function($) {
    if (cev_ajax.enableEmailVerification) {
        var otpVerified = false;

        function displayError(message) {
            var $form = $('#customer_login');
            $form.prepend('<div class="woocommerce-error">' + message + '</div>');
        }

        function clearErrors() {
            $('.woocommerce-error').remove();
        }

        // Set the value of email_verification hidden field to false
        $('input[name="email_verification"]').val('false');
        
        // Add a class to the register button if email_verification is false
        $('button[name="register"]').addClass('email_verification_popup');

        // Track in-flight requests so a second click can never fire a second
        // verification email while the first request is still running.
        var cevRegisterInFlight = false;
        var cevButtonLabel = '';

        function getButtonLabel($button) {
            return $button.is('input') ? $button.val() : $button.text();
        }

        function setButtonLabel($button, label) {
            if ($button.is('input')) {
                $button.val(label);
            } else {
                $button.text(label);
            }
        }

        // Put the register button into its processing state and show the loader.
        function startProcessing($button) {
            cevRegisterInFlight = true;
            cevButtonLabel = getButtonLabel($button);
            setButtonLabel($button, cev_ajax.cev_processing);
            $button.addClass('cev_processing').prop('disabled', true);
            $('.cev_loading_overlay').css('display', 'block');
        }

        // Restore the register button so the customer can act again.
        function stopProcessing($button) {
            cevRegisterInFlight = false;
            if (cevButtonLabel) {
                setButtonLabel($button, cevButtonLabel);
            }
            $button.removeClass('cev_processing').prop('disabled', false);
            $('.cev_loading_overlay').css('display', 'none');
        }

        // Handle the register button click event
        $(document).on('click', '.email_verification_popup', function(e) {
            if (!otpVerified && $(this).hasClass('email_verification_popup')) {
                e.preventDefault();

                // Ignore repeat clicks while the first request is still running.
                if (cevRegisterInFlight) {
                    return;
                }

                clearErrors();
                var $button = $(this);

                var $form = $(this).closest('form');
                var email = $form.find('#reg_email').val();
                var password = $form.find('#reg_password').val();

                if (!email) {
                    displayError(cev_ajax.cev_email_validation);
                    return;
                }
                if (cev_ajax.password_setup_link_enabled == "no") {
                    if (!password) {
                        displayError(cev_ajax.cev_password_validation);
                        return;
                    }
                }

                startProcessing($button);

                // AJAX request to check if the email is already registered
                $.ajax({
                    url: cev_ajax.ajax_url,
                    type: 'POST',
                    data: $('.woocommerce-form-register').serialize() + '&action=check_email_exists&nonce=' + cev_ajax.nonce,
                    success: function(response) {
                        var data = (response && response.data) ? response.data : {};

                        if (data.exists) {
                            displayError(cev_ajax.cev_email_exists_validation);
                            stopProcessing($button);
                        } else if (data.not_valid) {
                            displayError(cev_ajax.cev_valid_email_validation);
                            stopProcessing($button);
                        } else if (data.already_verify) {
                            // Re-enable the button first, otherwise the synthetic
                            // click below cannot submit the form.
                            stopProcessing($button);
                            $form.find('button[name="register"]').removeClass('email_verification_popup');
                            $form.find('input[name="email_verification"]').val('true');
                            $form.find('button[name="register"]').trigger('click');
                        } else if (data.email) {
                            stopProcessing($button);
                            $('#otp-popup').show();
                        } else if (data.validation == false) {
                            displayError(data.message);
                            stopProcessing($button);
                        } else {
                            stopProcessing($button);
                        }
                    },
                    error: function(xhr, status, error) {
                        displayError(cev_ajax.cev_error_prefix + ' ' + error);
                        stopProcessing($button);
                    }
                });
            }
        });

        // OTP input handling
        $('.otp-input').on('input', function() {
            var $this = $(this);
            var index = $this.index();
          
            if ($this.val().length === 1) {
                if (index < $('.otp-input').length - 1) {
                    $('.otp-input').eq(index + 1).focus();
                } else {
                    $('#verify-otp-button').trigger('click');
                }
            }
        });

        $('.otp-input').on('keydown', function(e) {
            var $this = $(this);
            var index = $this.index();

            if (e.keyCode === 8 && $this.val().length === 0) {
                if (index > 0) {
                    $('.otp-input').eq(index - 1).focus();
                }
            }
        });

        $('.otp-input').on('keypress', function(e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        });

        var cevOtpInFlight = false;

        $('#verify-otp-button').on('click', function(event) {
            event.preventDefault(); // Prevents page reload

            // Ignore repeat clicks while a verification request is running.
            if (cevOtpInFlight) {
                return;
            }
           
            function getOtpValue() {
                var otp = '';
                $('.otp-input').each(function() {
                    otp += $(this).val();
                });
                return otp;
            }

            var $form = $('.email_verification_popup').closest('form');
            var email = $form.find('#reg_email').val();
            var otp = getOtpValue();

            cevOtpInFlight = true;

            // Verify OTP using AJAX
            $.ajax({
                url: cev_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'verify_otp',
                    otp: otp,
                    email: email,
                    nonce: cev_ajax.nonce 
                },
                success: function(response) {
                   
                    if (response.success) {
                        
                        if (response.data.verified) {
                            $('#otp-popup').hide();

                            $('<p class="success-message">' + cev_ajax.cev_verified_success + '</p>').insertBefore($form);

                            // Make sure the button is enabled before the synthetic
                            // click, otherwise the form cannot be submitted.
                            $form.find('button[name="register"]').removeClass('email_verification_popup cev_processing').prop('disabled', false);
                            $form.find('input[name="email_verification"]').val('true');
                            $form.find('button[name="register"]').trigger('click');
                        } else {
                            displayError(response.data.message);
                        }
                    } else {
                        $('.error_mesg').text(response.data.message).css('display', 'block');
                    }
                },
                error: function(xhr, status, error) {
                    displayError(cev_ajax.cev_error_prefix + ' ' + error);
                },
                complete: function() {
                    cevOtpInFlight = false;
                }
            });
        });

        var cevResendInFlight = false;

        $('.send_again_link').on('click', function() {
            // Ignore repeat clicks while a resend request is running.
            if (cevResendInFlight) {
                return;
            }

            var $form = $('.woocommerce-form-register__submit').closest('form');
            var email = $form.find('#reg_email').val();

            cevResendInFlight = true;

            $.ajax({
                url: cev_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resend_otp',
                    email: email,
                    nonce: cev_ajax.nonce 
                },
                success: function(response) {
                    if (response.data.email) {
                        $('.resend_sucsess').show();
                        setTimeout(function() {
                            $('.resend_sucsess').hide();
                        }, 5000); // Hide after 5 seconds
                    } else {
                        console.log('No email found in response.data');
                    }
                },
                error: function(xhr, status, error) {
                    displayError(cev_ajax.cev_error_prefix + ' ' + error);
                },
                complete: function() {
                    cevResendInFlight = false;
                }
            });
        });
    }
    $(document).on('click', '.back_btn', function(e) {
        $('#otp-popup').hide();
    });
});
