=== Customer Email Verification for WooCommerce   ===
Contributors: zorem,gaurav1092,eranzorem,yashpatel1007
Tags: woocommerce, email address verification, customer verification, registration verification, woocommerce signup spam
Requires at least: 5.3
Requires PHP: 7.2
Tested up to: 6.8.2
Stable tag: 2.6.5
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Secure WooCommerce registrations with OTP-based email verification, reducing spam and ensuring only valid email addresses are used.

== Key Features==

* **🔑 OTP-Based Email Verification:** Customers must verify their email with an OTP before completing registration.
* **📩 Email Verification Popup:** The verification popup appears instantly after entering an email address and clicking the verify button.
* **❌ No Account Creation Without Verification:** Users cannot create an account unless they verify their email.
* **🎨 Customizable Verification Popup:** Modify the popup's design and messages to match your brand.
* **✉️ Customizable Verification Email:** Customize the OTP email template, subject, and message.
* **🔄 Resend OTP Option:** Customers can resend the OTP if they didn't receive the initial email.
* **🛠 Admin Verification Control:** View and manage email verification statuses from the WordPress admin panel.
* **🔓 Role-Based Verification Skipping:** Skip email verification for selected user roles. Redirect users to any page after successful email verification.

== Compatibility ==

Customer Email Verification for WooCommerce is built to integrate smoothly with plugins that follow WooCommerce’s standard registration and checkout templates. It also works with various social media login plugins, providing flexibility and convenience for users.

The following plugins have been tested and confirmed to be fully compatible:

* Checkout WC
* WooCommerce Social Login
* Nextend Social Login and Register
* WooCommerce Memberships
* WooCommerce Checkout & Funnel Builder by CartFlows
* Affiliate For WooCommerce
* Smart Manager
* Cashier

For a complete list of compatible plugins and more details, please visit our [documentation](https://docs.zorem.com/docs/customer-email-verification-pro/compatibility/).

### We also offer a Pro version! ###

== Customer Email Verification PRO ==

* **📦 OTP Verification for Checkout:** Enforce email verification for guest users before completing a purchase.
* **🛍️ Enable Checkout Verification:** Choose to verify emails on the cart page or only for free orders.
* **🔢 OTP Length Customization:** Select between 4-digit or 6-digit OTP codes for verification.
* **⏳ OTP Expiration Control:** Set expiration time for OTPs (e.g., 72 hours) to enhance security.
* **🔄 Verification Email Resend Limit:** Restrict the number of OTP resend attempts to prevent abuse.
* **🔐 Login Authentication Options:**
  * Notify users when they log in from a new device or browser.
  * Require OTP verification for logins from an unrecognized device, location, or after a set period.
  * Define specific conditions for unrecognized logins, such as logging in from a new device or a location not used before.
* **🛠 Advanced Customization:** More control over email templates and verification popups.

[Get CEV PRO >](https://www.zorem.com/product/customer-email-verification/)

== Other Plugins by zorem ==

Optimize your WooCommerce store with our plugins:

* [Advanced Shipment Tracking Pro](https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/)
* [Zorem Local Pickup Pro](https://zorem.com/plugins/zorem-local-pickup-pro/)
* [SMS for WooCommerce](https://zorem.com/plugins/sms-for-woocommerce/)
* [Country Based Restriction for WooCommerce](https://zorem.com/plugins/country-based-restriction-for-woocommerce/)
* [Sales By Country for WooCommerce](https://zorem.com/plugins/sales-by-country-for-woocommerce/)
* [Zorem Returns](https://zorem.com/plugins/zorem-returns/)
* [Email Reports for WooCommerce](https://zorem.com/plugins/email-reports-for-woocommerce/)
* [View as Customer for WooCommerce](https://zorem.com/plugins/view-as-customer-for-woocommerce/)

Explore more at [zorem.com](https://www.zorem.com/)
 
== Installation ==

1. Upload the folder `customer-email-verification-for-woocommerce` to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==
= 2.6.5 =
* Dev - Added a compatibility with WooCommerce 10.1.2

= 2.6.4 =
* Fix - Fix JavaScript Link for “Already Have a Verification Code?”
* Fix - Fix Tooltip Not Working with WooCommerce 10
* Dev - tested with WooCommerce  10.0.4
* Dev - tested with WordPress 6.8.2

= 2.6.3 =
* Fix - PHP Warning – Undefined Array 
* Fix - Grammar in Verification Page – Change “4-digits code” to “4-digit code”
* Fix - Verification widget not open in customizer
* Dev - tested with WooCommerce  9.9.5

= 2.6.2 =
* Dev - tested with WooCommerce 9.8.5
* Dev - tested with WordPress 6.8.1

= 2.6.1 =
* Fix - Function _load_textdomain_just_in_time was called incorrectly. 
* Dev - tested with WooCommerce 9.8.1
* Dev - tested with WordPress 6.8

= 2.6 =
* Dev - Updated the promotional notice on the settings page.
* Dev - tested with WooCommerce 9.7.1

= 2.5 =
* Fix - Resolved a fatal error where the class "cev_new_account_email_customizer" was not found
* Enhancement - Removed all console.log statements from JavaScript files to enhance performance and prevent unnecessary debugging output in the browser console
* Fix - Resolved the passcode verification issue by ensuring the user table is created properly during the plugin update process
* Dev - tested with WooCommerce 9.7.0

= 2.4 =
* Fix - Signup Email Verification Issue - Verification Code Doesn't Match

= 2.3 =
* Dev - Created new signup verification flow where users cannot create an account without verifying their email.
* Dev - Update New Design of Admin Page According new signup verification.
* Dev - Update Customizer According to New signup Verification Flow.
* Dev - tested with WordPress 6.7.2
* Dev - tested with WooCommerce 9.6.2

= 2.2 =
* Fix -Uncaught TypeError: Cannot read properties of undefined (reading 'top')
* Fix - PHP Notice:  Function _load_textdomain_just_in_time was called incorrectly
* Fix - Deprecated: explode(): Passing null to parameter
* Dev - tested with WordPress 6.7.1
* Dev - tested with WooCommerce 9.5.1

= 2.1 =
* Enhancement - Test with WPML 4.7 and update the documentation
* Dev - tested with WordPress 6.7
* Dev - tested with WooCommerce 9.4.2

= 2.0 =
* Enhancement - Add UTM link for all the external links to zorem.com
* Enhancement - Add the text domain in all the strings in customizer
* Dev - tested with WordPress 6.5.3
* Dev - tested with WooCommerce 8.9.1

= 1.8 =
* Fix - Broken HTML on Users page
* Fix- user approval function on backend isn't working.
* Dev - Tested with WooCommerce 8.5.1

= 1.7 =
* Enhancement - Improve design of Pro Banner
* Dev - Compatibility with PHP 8.2
* Dev - tested with WooCommerce 8.3.0
* Dev - tested with WordPress 6.4.1

= 1.6 =
* Dev - Tested with WordPress 6.0.2 and WooCommerce 6.9.4
* Dev - Added compatibility with new WooCommerce HPOS

= 1.5 =
* Dev - Tested with WordPress 6.0.2 and WooCommerce 6.9.4
* Dev - Tested with WooCommerce Multilingual 5.0.0 Beta
* Enhancement - Updated the settings page design

= 1.4.1 =
* Fix - Try Again link in Email verification popup

= 1.4 =
* Enhancement - Updated design of settings page
* Enhancement - Updated design of Go Pro page
* Dev - Improved the code security
* Dev - Tested with WP 5.8 and WC 5.5.2

= 1.3.9 =
* Enhancement - Tested WooCommerce customer email verification compatibility.
* Dev - Tested with WP 5.7.2 and WC 5.4.1

= 1.3.8 =
* Fix - PHP Fatal error:  Uncaught Error: Call to undefined function wc_cev_customizer() in /customer-email-verification-for-woocommerce/includes/customizer/class-cev-customizer.php:27

= 1.3.7 =
* Fix - Uncaught Error: Class 'cev_initialise_customizer_settings' not found in customer-email-verification-pro/includes/customizer/class-cev-seperate-email-customizer.php:20

= 1.3.6 =
* Enhancement - Updated settings page design.
* Enhancement - Set ajax on Email Verification and Actions panel on users listing page
* Dev - Tested with WP 5.7.1 and WC 5.2.1

= 1.3.5 =
* Enhancement - Improved the "Email Verification" link html in user verification email.
* Dev - Tested with WP 5.6 and WC 5.0.0

= 1.3.4 =
* Fix - For administrator user always shows the verification widget on my account page.
* Fix - Fixed issue with skip email verification for the selected user roles option.
* Dev - Tested with WP 5.6 and WC 4.9.2

= 1.3.3 =
* Enhancement - Setup My Account change email verification text.
* Enhancement - Customizer improvements.
* Dev - Tested with WP 5.6 and WC 4.9.2

= 1.3.2 =
* Enhancement - Updated settings page design.
* Enhancement - Add new customizer for the verification display on the new account email.
* Enhancement - Add new customizer for the verification widget style and verification widget message
* Enhancement - Remove customer view settings page tab
* Dev - Tested with WP 5.6 and WC 4.9.1

= 1.3.1 =
* Enhancement - Updated settings page design.
* Enhancement - Updated users list page design for Email Verification and actions panel
* Enhancement - Updated edit user page design for Email Verification panel
* Enhancement - Update Customer View settings page design and remove frontend message options
* Enhancement - Added functionality of live preview of  Verification widget
* Enhancement - Aaded verification sucess message in general settings.
* Dev - Tested with WP 5.6 and WC 4.8


= 1.3 =
* Enhancement - Added option for "Redirect to select page after verification".
* Dev - Tested with WC 4.5

= 1.2 =
* Fix - Fixed ERR_TO_MANY_REDIRECT issue in email verification page
* Dev - Tested with WordPress 5.5 

= 1.1 =
* Enhancement - merged the verification status fields in WordPress users admin and added actions icons.
* Enhancement - added option to  bulk actions resend verification email and verify user email address
* Fix - auto refresh the permalink

= 1.0.9 =
* Enhancement - Added a filter in Users list page for Verified and Non verified users
* Enhancement - Added a option for "Allow first login after registration without email verification"
* Fix - Fixed warnings Undefined offset: 0 in /includes/class-wc-customer-email-verification-admin.php on line 439

= 1.0.8 =
* Enhancement - change Navigation label
* Fix - Fixed settings page design issue
* Fix - Fixed user page layout issue
* Fix - Fixed warnings in Undefined index 0

= 1.0.7 =
* Dev - added functionality for skip email verification for already registered user

= 1.0.6 =
* Enhancement - Change design of enter your pin input box in email verification form
* Enhancement - Updated design of email verification form
* Dev - Change default value for email heading and email content

= 1.0.5 =
* Fix - Fixed Separate email subject issue
* Fix - Fixed WooCommerce email verification endpoint not found issue

= 1.0.4 =
* Enhancement - Added Enable/Disable option for customer email verification
* Enhancement - Added spinner and settings save message in settings page
* Fix - Fixed warnings in front end page

= 1.0.3 =
* Dev - Updated email verification process and added pin verification functionality
* Dev - After login block all my account page until user verify his email

= 1.0.2 =
* Dev - Tested with WordPress 5.4 and WooCommerce 4.0

= 1.0.1 =
* Fix - Fixed issue with WooCommerce email
* Fix - Fixed skip email verification for selected roles option save issue
* Fix - Fixed warnings from users list page

= 1.0 =
* Initial version.
