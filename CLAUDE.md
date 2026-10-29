# Customer Email Verification for WooCommerce (FREE)

## Project Overview

A WooCommerce plugin that reduces registration spam by requiring customers to verify their email address when they register an account, before they can access the My Account area.

**Current Version:** 2.7
**Author:** zorem
**License:** GPL-2.0+
**Text Domain:** `customer-email-verification-for-woocommerce`
**Requires:** WooCommerce, WordPress
**Distribution:** WordPress.org plugin directory

## Companion Pro Plugin

A paid Pro version exists in a separate repo at `D:\Zorem Project\CEV Pro\` (two builds: `customer-email-verification/` on WooCommerce Marketplace, `customer-email-verification-pro/` on zorem.com).

When the Pro plugin is active, the Free plugin **defers** (it boots only when `! $this->is_cev_pro_active() || ! $this->cev_pro_version_check()` — see main file). This means feature work happens primarily in the Pro repo; the Free plugin keeps the minimum free-tier feature set.

**Never copy license / Pro-only code into this repo.** No `cev-pro-license.php`, no `cev_license_tab.php`, no subscription/license gating.

## Plugin Architecture

### Main Plugin Class
`Zorem_Woo_Customer_Email_Verification` — singleton-style entry point in `customer-email-verification-for-woocommerce.php`.

### Directory Structure
```
├── assets/
│   ├── css/                # admin, customizer, front, signup styles
│   ├── js/                 # admin, customizer, front, signup scripts
│   ├── images/
│   └── zui/                # ⚠️ READ-ONLY shared Zorem UI library (do not edit)
├── includes/
│   ├── customizer/         # WP Customizer integration for email templates
│   ├── views/              # Admin page templates
│   │   ├── admin_options_settings.php   # Settings tab body
│   │   ├── activity_panel.php           # Old top-right menu (legacy)
│   │   ├── cev_users_tab.php            # Unverified users DataTable
│   │   ├── cev_addons_tab.php           # Go Pro upsell tab
│   │   ├── cev_pro_sidebar.php          # Right-rail upgrade card
│   │   ├── cev_signup_popup.php         # Frontend OTP popup
│   │   └── zui-page-header.php          # ZUI admin header (v2.7+)
│   ├── class-wc-customer-email-verification-admin.php
│   ├── class-wc-customer-email-verification-email.php
│   ├── class-wc-customer-email-verification-email-common.php
│   ├── class-wc-customer-email-verification-signup.php
│   ├── class-wc-customer-email-verification-installation.php
│   └── cev-wc-admin-notices.php
├── templates/emails/       # WooCommerce email templates
├── lang/                   # Translation files (.po, .mo, .pot)
├── changelog.txt
└── readme.txt
```

## Admin UI — Zorem UI (ZUI) design system

The admin is being migrated to the **shared Zorem UI library** at `assets/zui/`
(`css/ js/ images/ VERSION` etc.) and used **directly — no build step**.

### Golden rules

- **Reuse a library component before inventing markup.** Check `assets/zui/css/components/` (and `assets/zui/ZUI-COMPONENTS-PREVIEW.html`) first. The library already has cards, rows, fields, toggles, selects, checkboxes, tooltips, tables, tabs, badges, snackbars, the license page, etc.
- **Plugin-only styling → `assets/css/cev-admin.css`, scoped under `.zui-scope.cev-admin`.** Never add plugin-specific cosmetics to the library.
- **⚠️ NEVER edit anything inside `assets/zui/` — it is a shared, read-only library.** Do **not** modify its CSS/JS and do **not** bump its `VERSION` / `CHANGELOG.md` from this repo. If the library is missing a primitive or has a bug, add the styling/markup in the plugin instead (`assets/css/cev-admin.css`, scoped under `.zui-scope.cev-admin`) using a custom class, and flag the gap to the library maintainer out-of-band.
- **Every custom class name must start with `cev_` or `cev-`.** Plugin-specific classes you add (in `cev-admin.css` or markup) use one of these prefixes so they never collide with the generic library classes.
- **Brand registry caveat for FREE plugin.** `assets/zui/brand.php` ships with the Pro plugin entry registered, not the Free one. Since the library is read-only here, hardcode the Free brand inline in `zui-page-header.php` (name=CEV, badge=FREE, tagline=Customer Email Verification, icon=globe, emblem blue) per the documented fallback ("Returns NULL if the slug is unknown so the caller can fall back to a hardcoded brand").

### How the admin is assembled

- **Enqueue** — `admin_styles()` in `class-wc-customer-email-verification-admin.php` loads `zui` (css+js) from `assets/zui/` (version = the VERSION file), then `cev-admin-overrides` (`assets/css/cev-admin.css`, depends on `zui`).
- **Scope wrapper** — the new header renders inside `<div class="zui-scope cev-admin" id="cev-admin-app">`. `.zui-scope` = library tokens/reset; `.cev-admin` = the CEV override hook.
- **Header partial** — `includes/views/zui-page-header.php` renders the brand cluster (emblem + name + FREE badge + tagline) on the left and actions (Docs Portal + bell) on the right, then the tab strip.
- **Top tabs** — defined by `get_cev_tab_settings_data()` (admin class), rendered by `render_zui_menu_tabs()`. They are real `?tab=` links that reload the page; the existing body's radio-based panel switcher reads `$_GET['tab']` to pick the active panel.

## Development Guidelines

### Shared library is read-only — never edit `assets/zui/`
The `assets/zui/` folder is a shared library used by every Zorem plugin; treat it as **read-only** in this repo. Never modify its CSS/JS and never bump its `VERSION` / `CHANGELOG.md` here. If the library is missing something or has a bug, add the styling/markup in the plugin instead — custom CSS in `assets/css/cev-admin.css` scoped under `.zui-scope.cev-admin`. **All custom class names must start with `cev_` or `cev-`** so they never collide with the library.

### Reporting modified files (ALWAYS)
After completing any change, **list the full path of every file you added or modified** at the end of your response — one full path per line. If nothing was written to disk, say so explicitly.

### WordPress/WooCommerce Standards
- Follow WordPress Coding Standards for PHP
- Use WordPress hooks (`add_action`, `add_filter`) for extensibility
- Use `wp_ajax_` and `wp_ajax_nopriv_` for AJAX endpoints
- Sanitize all inputs (`sanitize_text_field`, `absint`, etc.)
- Escape all outputs (`esc_html`, `esc_attr`, `wp_kses`, etc.)
- Use nonces for form security (`wp_nonce_field`, `wp_verify_nonce`)
- Use `__()` and `esc_html__()` with text domain `customer-email-verification-for-woocommerce` for translations

### Internationalization (i18n) — NEVER miss the text domain (REQUIRED)
- Every user-facing string must use a translation function (`__()`, `_e()`, `esc_html__()`, `esc_html_e()`, `esc_attr__()`, `esc_attr_e()`, `_n()`, `_x()`).
- The **only** valid text domain in this plugin is **`customer-email-verification-for-woocommerce`**. Any other domain is a silent translation bug.
- **Plugin Check reports every mismatch as an ERROR** (`WordPress.WP.I18n.TextDomainMismatch`), and WordPress.org blocks submission on it. Treat a domain typo as a release blocker, not a nit.
- **Domains that have actually shipped in this repo by mistake** — check for all of them, not just the obvious ones:
  - `'woocommerce'` — copy-pasted from WooCommerce core snippets (was in the main plugin file's action links).
  - `'customer-emial-verification-for-woocommerce'` — **`emial` typo**. Easy to miss on a read-through; only a scan catches it.
  - `'zorem-ui'` — comes from a **stale** `assets/zui/` sync; fixed in ZUI 1.9.2, see below.
- Never write a domain literal by hand. Copy it from an adjacent line in the same file.

### ⚠️ NEVER use `_e()` / `esc_html_e()` as a substitute for `echo` (REQUIRED)

This was the single biggest source of Plugin Check errors in the 2.9 audit — 74 call
sites, ~150 error rows. The settings renderer had been written as if `esc_html_e()`
were a shorthand for `echo esc_html()`:

```php
// ❌ Wrong — every one of these is TWO Plugin Check errors
//    (NonSingularStringLiteralText + MissingArgDomain)
<input id="<?php esc_html_e( $id ); ?>" <?php esc_html_e( $checked ); ?>>
<p><?php esc_html_e( $array['title'] ); ?></p>
<?php echo esc_html_e( time() ); ?>
```

`__()` / `_e()` / `esc_html_e()` look up their argument in the translation catalogue.
That catalogue is keyed on **literal strings extracted from the source at build time**,
so passing a variable, a function call, or a number can never match anything — the call
does nothing except cost a lookup. It is also the **wrong escaper** for HTML attributes.

**The `$text` argument must always be a single, literal, quoted string.** Never a
variable, never a concatenation, never `sprintf()`, never a number.

To print a runtime value, use a plain `echo` with the escaper the context requires:

| Context | Use |
|---|---|
| HTML attribute value — `id="…"`, `name="…"`, `title="…"`, `value="…"` | `echo esc_attr( $v );` |
| Bare attribute flag — `$checked`, `$selected`, `$multiple` | `echo esc_attr( $v );` |
| Text between tags | `echo esc_html( $v );` |
| `<textarea>` content | `echo esc_textarea( $v );` |
| URL in `href` / `src` | `echo esc_url( $v );` |

**Don't double-translate.** Settings arrays already wrap their `title` / `tooltip` /
`placeholder` in `__()` where they are *defined*. At the render site they only need
escaping. Same for `$this->defaults[...]` — wrapping it in `__()` again is a no-op on a
runtime value AND a Plugin Check error.

**Every i18n call needs its `$domain` argument**, and it is a different position per
function: `__( $t, $d )`, `_x( $t, $ctx, $d )`, `_n( $s, $p, $n, $d )`, `_nx( $s, $p, $n, $ctx, $d )`.
Omitting it silently falls back to WordPress core's `default` domain — Plugin Check
reports `MissingArgDomain`. This bit `submit_button( __( 'Filter' ) )` and the
`wpColorPickerL10n` block, both of which looked fine because core happened to have
translations for those exact strings.

**`/* translators: */` must sit immediately above the i18n call — not above a wrapper:**
PHPCS attributes the comment to whatever token follows it. Wrapping the call in
`esc_html( ... )` on the next line makes the comment describe `esc_html`, and
`MissingTranslatorsComment` fires on the `_n()` inside.

```php
// Wrong — comment lands on esc_html(), not on _n()
printf( '<p>' .
    /* translators: %s: replace with email */
    esc_html( _n( 'Sent to %s user.', 'Sent to %s users.', $count, $domain )
) . '</p>', $count );

// Correct — assign the translated string first, comment directly above _n()
/* translators: %s: number of users the verification email was sent to. */
$notice = _n(
    'Verification email sent to %s user.',
    'Verification email sent to %s users.',
    $count,
    'customer-email-verification-for-woocommerce'
);
echo '<p>' . esc_html( sprintf( $notice, $count ) ) . '</p>';
```

**Use `wp_safe_redirect()`, not `wp_redirect()` — `WordPress.Security.SafeRedirect`:**
`wp_safe_redirect()` restricts the destination to the site's own host (extend it via the
`allowed_redirect_hosts` filter), which closes off open-redirect abuse. Always follow the
call with `exit;` so nothing after it runs.

```php
// Wrong
wp_redirect( home_url() . '/my-account/' );
exit;

// Correct
wp_safe_redirect( home_url() . '/my-account/' );
exit;
```

### PrefixAllGlobals warnings — do NOT "fix" these by renaming

Plugin Check reports a large block of `WordPress.NamingConventions.PrefixAllGlobals`
warnings. Two different things are going on, and only one is worth acting on.

**1. Variables in `includes/views/*.php` — FALSE POSITIVES, already suppressed.**
Every view is `include`/`require`d from inside a class method, so its top-level
variables are function-scoped, not global. PHPCS analyses each file in isolation and
cannot see that. Each view carries a file-level
`// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound`
with that reason. Keep it when adding a new view.

**2. Functions / classes / hook names — LEAVE THEM.**
Plugin Check derives the accepted prefix from the plugin slug, so it only accepts
something like `customer_email_verification_for_woocommerce_`. Proof: `cev_`-prefixed
names such as `cev_verification_email_content` and `$cev_button_color_widget_header`
are still flagged. Satisfying the rule would mean renaming:

- `woo_customer_email_verification()` — the global accessor used across the plugin
  and by CEV PRO
- every `cev_*` filter — the documented public API customers hook into
- classes like `WC_Customer_Email_Verification_Customize`

That is a breaking change to a shipped public API to silence a **warning** on a plugin
already live on WordPress.org. Do not do it unless a wp.org reviewer explicitly asks.

Three subsets are not ours to prefix at all: the `Zorem\UI` namespace in the read-only
`assets/zui/`, `zorem_tracking()` from the shared SDK, and core hooks the plugin fires
(`woocommerce_registration_errors`, `woocommerce_mail_content`, `wp_login`, ...) —
prefixing those would break the integration outright.

### ⚠️ Every `cev_` hook needs its `phpcs:ignore` on the line DIRECTLY above (REQUIRED)

Because we keep the `cev_` hook names (see above), every `apply_filters()` / `do_action()`
that fires a `cev_` hook must carry this comment **on the line immediately above the line
that contains the hook name**:

```php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established public filter of this plugin; renaming breaks existing integrations.
$value = apply_filters( 'cev_some_filter', $value );
```

Plugin Check reports `NonPrefixedHooknameFound` for every hook that breaks this. Three
ways it has actually broken in this repo (2.9 work: 4 warnings):

1. **A line inserted between the ignore and the hook.** Adding a code comment or an
   assignment in between makes the ignore apply to the wrong line.
   ```php
   // ❌ Wrong — the ignore now covers the comment line, not the hook
   // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- ...
   // OTP Length setting: '2' = 6 digits.
   $digits = apply_filters( 'cev_verification_code_length', $default );

   // ✓ Correct — your comment goes ABOVE the ignore
   // OTP Length setting: '2' = 6 digits.
   // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- ...
   $digits = apply_filters( 'cev_verification_code_length', $default );
   ```
2. **A new `cev_` hook added without the comment at all.** New hooks need it too — a
   docblock above the hook does not count.
3. **A multi-line call with the hook name on its own line.** The warning is reported on the
   line holding the hook name, so an ignore above `apply_filters(` misses it. Keep the hook
   name on the same line as `apply_filters(` / `do_action(` (move a long default into a
   variable first).
   ```php
   // ❌ Wrong — warning lands on the 'cev_pro_offer_details' line
   $offer = apply_filters(
   	'cev_pro_offer_details',
   	array( 'price' => '$119' )
   );

   // ✓ Correct
   $offer_defaults = array( 'price' => '$119' );
   // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Plugin's established cev_ hook prefix (public API used by CEV PRO and integrations).
   $offer = apply_filters( 'cev_pro_offer_details', $offer_defaults );
   ```

When you edit any line next to a `cev_` hook, re-check that its ignore is still directly
above it. Pre-flight scan **#32** catches all three cases.

### Plugin Check (WordPress.org) — run before every release

**⚠️ FIRST: make sure Plugin Check is reading THIS repo.** wp-admin scans the plugin
folder inside the WordPress install, which on this machine is a **separate copy** from
the repo:

- repo (edited here): `D:\Zorem Project\customer-email-verification-for-woocommerce`
- WAMP install (scanned): `C:\wamp64\www\sms-dokan-store\wp-content\plugins\customer-email-verification-for-woocommerce`

They are unrelated directories, so edits on `D:` never reach the scanner and Plugin Check
keeps reporting errors that were already fixed. WAMP also carries three CEV variants
(`customer-email-verification`, `-pro`, `-for-woocommerce`) across four sites, so confirm
which one the dropdown targets.

Before trusting any Plugin Check result, verify the version in wp-admin's plugin list
matches `$this->version` in the repo. If it doesn't, the report is about different code.

Link them once with a junction so they can never drift:

```
mklink /J "C:\wamp64\www\sms-dokan-store\wp-content\plugins\customer-email-verification-for-woocommerce" "D:\Zorem Project\customer-email-verification-for-woocommerce"
```

`Tools → Plugin Check` in wp-admin. Enable all categories and both Error + Warning types.
Everything under **ERROR** must be zero before the plugin is submitted to WordPress.org.

The result panel has **Export CSV / JSON / CTRF / Markdown** buttons. **Always export the
report and work from the export**, not from the on-screen list — the screen shows only the
first few files and the run can be hundreds of rows across dozens of files.

#### `assets/zui/` and text domains — RESOLVED in ZUI 1.9.2

The ZUI library used to hardcode `'zorem-ui'` as the text domain in
`assets/zui/eco-plugins.php`, which produced `TextDomainMismatch` errors in every
consumer plugin. **ZUI 1.9.2 fixed this at the source**: the registry now returns raw
English strings for `name` / `desc` / `badge` / `stat` and calls no i18n function at
all, so the host plugin translates them with its own domain.

If you see `zorem-ui` in a Plugin Check run, the fix is to **sync `assets/zui/` to
1.9.2 or later** — never to edit the library in this repo, which the next sync would
revert. `brand.php` and `icons.php` contain no i18n calls.

Note that `eco-plugins.php` is currently not referenced anywhere in this plugin.

### Version Bumping
**⚠️ Only bump the plugin version and edit `changelog.txt` when the user EXPLICITLY instructs it.** Do NOT change the version or changelog as part of implementing a feature, fix, or "release hygiene" on your own initiative — even if the change feels release-ready. If you change the version or `changelog.txt` without being asked, revert it.

When the user DOES ask for a version bump, update version in:
- Main plugin file header (`Version:`)
- `$this->version` property in the main class
- `changelog.txt`

---

# PHPCS / Code Style — must follow (sniffer is run on every release)

> The rules below mirror the Pro plugin's CLAUDE.md and apply identically here. Every change to PHP code in this plugin must pass the same WordPress PHPCS ruleset.

**Whitespace rules — CRITICAL:**
- **Exactly ONE space after a comma in function calls.** Never use vertical alignment with multiple spaces between commas and the next argument:
  ```php
  // ❌ Wrong — sniffer flags "Expected 1 space after comma; N found"
  update_option( 'cev_last_cleanup_at',    current_time( 'mysql' ),     false );
  $this->update_settings( $maintenance_settings,  $sanitized_post );
  $page_size = max( 50,   min( 2000,   $page_size ) );

  // ✓ Correct
  update_option( 'cev_last_cleanup_at', current_time( 'mysql' ), false );
  $this->update_settings( $maintenance_settings, $sanitized_post );
  $page_size = max( 50, min( 2000, $page_size ) );
  ```
- This applies to function calls AND function-call-style helpers (`apply_filters`, `min`, `max`, `array`, etc.)
- Vertical alignment in array key→value with trailing comments is fine (idiomatic WP); the rule is about consecutive arguments separated by `, `

**Yoda conditions — REQUIRED:**
WordPress standard requires literal-first comparisons. PHPCS flags every non-Yoda check ("Use Yoda Condition checks, you must.").
```php
// ❌ Wrong
if ( $word !== '' ) { ... }
$x = $value === '' ? 'default' : $value;
if ( $status == 'active' ) { ... }

// ✓ Correct
if ( '' !== $word ) { ... }
$x = '' === $value ? 'default' : $value;
if ( 'active' === $status ) { ... }
```
Applies to all comparison operators (`===`, `!==`, `==`, `!=`, `<`, `>`) where one side is a literal (string, int, true, false, null) and the other is a variable.

**The rule applies through casts too** — a cast doesn't make the variable into a "literal" side; PHPCS still treats the cast expression as the variable side:
```php
// ❌ Wrong — cast-then-variable on the left still counts as variable side
if ( (string) $val === '1' ) { ... }
if ( (int) $count !== 0 )    { ... }

// ✓ Correct
if ( '1' === (string) $val ) { ... }
if ( 0 !== (int) $count )    { ... }
```

**Embedded `<?php ... ?>` in HTML templates — REQUIRED:**
PHPCS rule: opening AND closing tag of a multi-line embedded PHP block must each be on their own line. The only allowed alternative is a fully single-line embedded `<?php ... ?>` block.
```php
// ❌ Wrong — opening tag has HTML before it on same line, multi-line body
aria-label="<?php
    /* comment */
    printf( esc_attr__( '...', 'customer-email-verification-for-woocommerce' ), esc_attr( $email ) );
?>"

// ❌ Wrong — closing tag has HTML after it on same line, multi-line body
<td><?php
    // comment
?><table>

// ✓ Correct — single-line inline
aria-label="<?php printf( esc_attr__( '...', 'customer-email-verification-for-woocommerce' ), esc_attr( $email ) ); ?>"

// ✓ Correct — opening + closing on their own lines, HTML before/after on separate lines
<?php
// long-form comment / multi-line PHP logic
?>
<td><table>...</table></td>
```
For long blocks of explanatory comments next to inline HTML, place the `<?php ... ?>` block on its own paragraph BEFORE the HTML, then keep the HTML on its own line(s).

**Other recurring sniffer issues to avoid:**
- Always use `wp_unslash()` before sanitizing `$_POST` / `$_GET` / `$_REQUEST` data
- Always interpolate table names manually (`$wpdb->prefix . 'cev_user_log'`) but use `$wpdb->prepare()` placeholders for values
- **Wherever you add a MySQL query that contains variables (e.g. `{$wpdb->prefix}table`), wrap it with the block form:**
  ```php
  // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
  $result = $wpdb->get_var( ... );
  // phpcs:enable
  ```
  Do NOT use the old single-line `// phpcs:ignore` for these rules.
- Never `die()` or `exit` without a clear reason; use `wp_die()` for admin contexts

**`$_POST` / `$_GET` access without a nonce — PHPCS will flag it:**
PHPCS rule `WordPress.Security.NonceVerification.Recommended` /
`...Missing` fires whenever superglobals are read before a
`wp_verify_nonce()` / `check_admin_referer()` call. Fix patterns:

```php
// ✓ Correct — sanitize the nonce value, then verify, then access other fields.
if ( ! isset( $_POST['my_nonce'] ) ) {
    return;
}
$my_nonce = sanitize_text_field( wp_unslash( $_POST['my_nonce'] ) );
if ( ! wp_verify_nonce( $my_nonce, 'my_action' ) ) {
    return;
}
$value = sanitize_text_field( wp_unslash( $_POST['field'] ) );

// ✓ Also correct — suppress with a comment explaining the alt. defence
// (cryptographic token verification downstream, public-facing read, etc.)
// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
$parked = $this->load_parked( $token ); // verifies HMAC + expiry
```

Only use `phpcs:disable` when there's a real reason. Always pair the suppression with a comment that states the alternate defence so a future reader doesn't think it's a gap.

**Suppress BOTH `.Missing` and `.Recommended` together** — they're separate sniffer rules and a `phpcs:ignore` for one doesn't silence the other:

```php
// ❌ Common mistake — only suppresses .Recommended; .Missing still fires
$range = isset( $_POST['range'] ) ? sanitize_key( wp_unslash( $_POST['range'] ) ) : '30d'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// ✓ Correct — both rules listed
// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
$range = isset( $_POST['range'] ) ? sanitize_key( wp_unslash( $_POST['range'] ) ) : '30d';
```

**⚠️ `phpcs:disable`/`enable` blocks for `NonceVerification` are UNRELIABLE on audit-server PHPCS versions — DO NOT USE.** Tested multiple times: block-form suppressions inside a method body are IGNORED by the audit server's PHPCS, even when the same rule list works perfectly as an inline `phpcs:ignore` on the immediately preceding line. **Always use the inline (per-line) form for `NonceVerification`** — one `// phpcs:ignore` per superglobal access, with both `.Missing` and `.Recommended` listed.

- **For single-line `$_POST` / `$_GET` access**: previous-line `// phpcs:ignore` with **BOTH** rule names. Works reliably.
- **For multiple `$_POST` reads in one method**: the cleanest fix is NOT to suppress 5 lines in a row — it's to **refactor**. Move all `$_POST` reads to the caller (typically the AJAX handler that already does the nonce check) and pass them as parameters into the helper:

```php
// ✓ Best — refactor so the helper takes the inputs as parameters.
//   $_POST never appears in the helper, no PHPCS suppression needed.
public function ajax_data() {
    if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) { ... }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
    $range_key = isset( $_POST['range'] ) ? sanitize_key( wp_unslash( $_POST['range'] ) ) : '30d';

    list( $from, $to ) = $this->resolve_range( $range_key );
}

// Pure function — no $_POST, no PHPCS noise.
private function resolve_range( $range_key ) { ... }
```

**Passwords are exempt from sanitization — the InputNotSanitized sniff still fires anyway:** WordPress convention is that a user's password is used verbatim (only `wp_unslash()`-ed). `sanitize_text_field()` would silently strip valid password characters (tags `< > `, control whitespace, etc.) — that's a security regression, not a hardening. PHPCS doesn't know this and still flags the read. Always pair `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` with the two NonceVerification rule names:

```php
// ✓ Correct — split isset() and wp_unslash() onto their own lines so each
// $_POST['password'] read sits directly below its own phpcs:ignore.
$password = '';
// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
if ( isset( $_POST['password'] ) ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $password = (string) wp_unslash( $_POST['password'] );
}
```

**Centralised `$cev_print_html()` helper — the DEFAULT fix for any template with 3+ unsafe echoes:**
The multi-line embedded `<?php ?>` block is fine for one or two unsafe echoes. For anything with 3+ unsafe echoes, refactor: define a tiny closure at the top of the file that does the unsafe echo internally with ONE centralised `phpcs:ignore`, then route every callsite through it.

```php
// ✓ At the top of the template (next to the existing $cev_icon closure)
$cev_print_html = function ( $html ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Callers pass SVG / HTML built from constant maps with no user input; wp_kses_post would strip required SVG attributes.
    echo $html;
};

// ✓ Every callsite — no echo, no trailing comment, no PHPCS hit
<span class="zui-section-header__icon"><?php $cev_print_html( $cev_icon( 'mail' ) ); ?></span>
```

**Multi-line embedded `<?php ?>` block — opening tag, comment, echo, closing tag each on their own line (this is what reliably suppresses `EscapeOutput`):** Trailing same-line `// phpcs:ignore` after `<?php echo $var; ?>` is unreliable for `EscapeOutput` AND so is the block-form `phpcs:disable/enable` on some audit servers. The reliable form for ALL versions is:

```php
// ❌ Wrong — same-line trailing ignore, sometimes ignored by audit-server PHPCS
<span class="zui-section-header__icon"><?php echo $cev_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

// ❌ Wrong — block-form disable/enable, also unreliable on some audit servers
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
echo $cev_2fa_card_html;
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

// ✓ Correct — opening tag on its own line, previous-line ignore, echo, closing
//             tag on its own line. Reliable across every PHPCS version.
<span class="zui-section-header__icon">
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $cev_icon returns a constant SVG built from a static path map.
    echo $cev_icon( $icon );
    ?>
</span>
```

**Ternary expressions inside `echo` confuse `EscapeOutput` — even when BOTH branches call `esc_html__()`:**
PHPCS doesn't trace into ternary branches reliably. Split into a proper if/else inside the template, with each branch calling `esc_html_e()`:

```php
// ❌ Wrong — PHPCS flags the `==` and `:` even though both branches are escaped
<span>
    <?php echo ( 1 == $lic_connect && '' != $lic_email )
        ? esc_html__( 'Activate License', 'customer-email-verification-for-woocommerce' )
        : esc_html__( 'Connect & Activate License', 'customer-email-verification-for-woocommerce' );
    ?>
</span>

// ✓ Correct — explicit if/else with esc_html_e in each branch
<span>
    <?php if ( 1 == $lic_connect && '' != $lic_email ) : ?>
        <?php esc_html_e( 'Activate License', 'customer-email-verification-for-woocommerce' ); ?>
    <?php else : ?>
        <?php esc_html_e( 'Connect & Activate License', 'customer-email-verification-for-woocommerce' ); ?>
    <?php endif; ?>
</span>
```

**Never reassign WordPress globals (`$post`, `$wp_query`, `$page`, `$m`, `$paged`, `$pagename`, `$id`, `$tag`, …) — `WordPress.WP.GlobalVariablesOverride`:**
WordPress core declares a long list of globals. Reassigning any of them in template code — even as a throwaway loop variable — is a sniffer error AND a real bug. Pick a CEV-prefixed local name:

```php
// ❌ Wrong — `$m` is the WP month-archive global
foreach ( $cev_sections as $key => $section ) {
    $m = $cev_sections[ $key ];                  // overrides WP global
    $cev_section_header( $key, $m[0], $m[1], $m[2] );
}

// ✓ Correct — CEV-prefixed local, no collision with WP core
foreach ( $cev_sections as $key => $section ) {
    $cev_section_meta = $cev_sections[ $key ];
    $cev_section_header( $key, $cev_section_meta[0], $cev_section_meta[1], $cev_section_meta[2] );
}
```

**Forbidden one-letter / WP-global names** that PHPCS will reject even for purely-local use in template code:
`$post`, `$posts`, `$pages`, `$page`, `$paged`, `$pagename`, `$wp_query`, `$wp_rewrite`, `$current_user`, `$current_site`, `$is_iphone`, `$is_safari`, `$id`, `$tag`, `$cat`, `$comment`, `$m`, `$w`, `$year`, `$monthnum`, `$day`, `$hour`, `$minute`, `$second`, `$multipage`, `$more`, `$numpages`, **`$type`** (post-type global — easy to use as a `$field['type']` shorthand and get bitten).

When in doubt, prefix with `cev_` or use a descriptive name — never use bare `$m`, `$id`, `$post`, etc. in CEV template scope.

**Alternative-syntax `<?php if (...) : ?>...<?php endif; ?>` crammed on one line — `Generic.WhiteSpace.ScopeIndent.IncorrectExact`:**
PHPCS treats the `:` after `<?php if (...) :` as the equivalent of an opening brace `{`. Just like braces, it expects a newline before the body.

```php
// ❌ Wrong — body on same line as the `: ?>`
<?php if ( $cev_wrap_card ) : ?><div class="zui-card"><?php endif; ?>

// ✓ Correct — each `<?php : ?>` block on its own line, body indented under it
<?php if ( $cev_wrap_card ) : ?>
	<div class="zui-card">
<?php endif; ?>
```

**⚠️ Audit-server PHPCS sometimes flags alt-syntax even when the body IS on its own line.** The fix is the same: **prefer pure-PHP brace syntax with a literal `echo` for any simple wrapper conditional that only outputs static HTML.**

```php
// ✓ Bulletproof — works on every PHPCS version. Use this for any
//   wrap-card / wrap-section / wrap-tag conditional in a template.
<?php
if ( $cev_wrap_card ) {
	echo '<div class="zui-card">';
}
// ... rest of template body ...
if ( $cev_wrap_card ) {
	echo '</div>';
}
```

**When to use which form:**
- Wrapper conditionals that emit ONE static tag (`<div class="zui-card">`, `<section>`, `</main>`) — use pure-PHP `if () { echo '...'; }`.
- Conditionals that emit DYNAMIC markup with mixed PHP/HTML (e.g. a `<label>` with translated text + tooltip) — use alt-syntax `<?php if : ?>...<?php endif; ?>` with the body on its own line.

**Previous-line `phpcs:ignore` does NOT cover superglobals nested inside a multi-condition `if (...)` expression on the same line:**
The fix is **structural, not cosmetic**: pull the superglobal read into its own statement first, then test the local variable in the `if`.

```php
// ❌ Wrong — phpcs:ignore is ignored on lines 2 + 4 by some sniffers
// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
if ( $is_post && isset( $_POST[ self::PARK_KEY ] ) ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
    $token = sanitize_text_field( wp_unslash( $_POST[ self::PARK_KEY ] ) );
}

// ✓ Correct — read each superglobal on its own line so the ignore clearly applies.
// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
$post_token = isset( $_POST[ self::PARK_KEY ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::PARK_KEY ] ) ) : '';

$token = '';
if ( $is_post && '' !== $post_token ) {
    $token = $post_token;
}
```

**Sanitize EVERY superglobal read — including the nonce value itself — `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized`:**

```php
// ❌ Wrong — raw $_POST value handed to wp_verify_nonce()
if ( ! wp_verify_nonce( $_POST['my_form_nonce'], 'my_form' ) ) {
    return;
}

// ✓ Correct — sanitize then verify
$my_form_nonce = sanitize_text_field( wp_unslash( $_POST['my_form_nonce'] ) );
if ( ! wp_verify_nonce( $my_form_nonce, 'my_form' ) ) {
    return;
}
```

**`} elseif` / `} else` must stay on the same line — `Squiz.ControlStructures.ControlSignature`:**

```php
// ❌ Wrong — comment forces a newline between `}` and `elseif`
if ( 'deactivate' === $action ) {
    ...
}
// Handle activation
elseif ( 'activate' === $action ) {
    ...
}

// ✓ Correct — `} elseif` stays on one line; comment moved inside
if ( 'deactivate' === $action ) {
    ...
} elseif ( 'activate' === $action ) {
    // Handle activation
    ...
}
```

**Unescaped echo of a known-safe variable — `WordPress.Security.EscapeOutput.OutputNotEscaped`:**
Three ways to satisfy the sniff:

1. **Add a real escape** (preferred): `echo esc_html( $value );` / `echo wp_kses_post( $html );`
2. **Use the block form** (most reliable on older PHPCS): `// phpcs:disable ... \n echo $html; \n // phpcs:enable ...`
3. **Use the previous-line form**: `// phpcs:ignore ... -- reason\necho $html;`

**⚠️ Avoid the inline (same-line) suppression for this rule** — some PHPCS versions don't honour it for `EscapeOutput`.

**Doc comment long description — must start with a capital letter:**
`Squiz.Commenting.DocComment.ShortNotCapital` / `...LongNotCapital` fires when the description block in a docblock starts with a lowercase character.

```php
// ✓ Correct — capitalize the leading word of each bullet/label
/**
 * Render the bucket label.
 *
 *   Day:   "May 12"
 *   Week:  "May 12"
 */
```

**`$_COOKIE` access also needs sanitization:**
```php
// ✓ Correct — sanitize even though the payload is HMAC-checked downstream.
$raw     = sanitize_text_field( wp_unslash( $_COOKIE['my_cookie'] ) );
$payload = $this->verify_cookie( $raw );
```

**Increment / decrement inside expressions — BANNED:**
`Squiz.Operators.IncrementDecrementUsage` flags both `$pad = $bytes[ $i++ ]` AND `$p += 1;`.

```php
// ✓ Correct — read first, then bump on its own line
$pad = $pad_bytes[ $pi % 2 ];
++$pi;

// ✓ Correct — pre-increment instead of `+= 1`
++$p;
```

**One statement per line — REQUIRED:**
```php
// ❌ Wrong
$run = 0; $last = -1;

// ✓ Correct
$run  = 0;
$last = -1;
```

**Function body on the same line as the declaration — BANNED:**
```php
// ❌ Wrong
public static function mask0( $y, $x ) { return 0 === ( ( $y + $x ) % 2 ); }

// ✓ Correct
public static function mask0( $y, $x ) {
    return 0 === ( ( $y + $x ) % 2 );
}
```

**Single quotes when double quotes aren't needed — REQUIRED:**
`Squiz.Strings.DoubleQuoteUsage`. Default to single quotes; switch to doubles only for variable interpolation, escape sequences, or an internal apostrophe.

```php
// ❌ Wrong
__( "After verification, skip the prompt.", 'customer-email-verification-for-woocommerce' );

// ✓ Correct
__( 'After verification, skip the prompt.', 'customer-email-verification-for-woocommerce' );

// ✓ Correct — apostrophe inside justifies double quotes
__( "Don't remember — ask for code every login.", 'customer-email-verification-for-woocommerce' );
```

**Tabs-only indentation — REQUIRED (no tab+space mix):**
PHPCS rule "Tabs must be used to indent lines; spaces are not allowed". The most common offender is multi-line HTML comments where continuation lines align under `<!-- `:

```html
<!-- ❌ Wrong — tabs to indent, then spaces to align under "<!-- " -->
				<!-- Step 1: Add to your authenticator app (two options:
				     scan QR or copy key). -->

<!-- ✓ Correct — break the comment onto its own block; pure tab indent -->
				<!--
					Step 1: Add to your authenticator app (two options:
					scan QR or copy key).
				-->
```

**Exception:** content INSIDE string literals (multi-line SQL in `$wpdb->prepare()`, heredocs, etc.) is not "indentation" — those tabs+spaces are part of the string value, and PHPCS doesn't flag them.

## Before submitting any release — pre-flight scans

All scans should return zero/empty before shipping. If any matches, fix them and re-scan.

```bash
# 1. Multi-space after comma in function calls (sniffer flags)
grep -nE ',  +[^/]' includes/**/*.php

# 2. Non-Yoda comparisons (variable on left of literal). Matches both
#    bare `$var === 'x'` AND cast-then-variable `(string) $val === '1'`.
grep -nE '(\([a-zA-Z_]+\)\s*)?\$[a-zA-Z_][a-zA-Z0-9_>-]* +[!=]==? +('"'"'[^'"'"']*'"'"'|"[^"]*"|true|false|null|[0-9]+)' includes/**/*.php

# 3. Embedded PHP tags with HTML on same line + multi-line body
grep -nE '[^[:space:]]+<\?php[[:space:]]*$|^[[:space:]]*\?>[^[:space:]]+' includes/**/*.php templates/**/*.php

# 4. Brace balance per modified file (must be 0)
awk 'BEGIN{c=0} {for(i=1;i<=length($0);i++){ch=substr($0,i,1); if(ch=="{")c++; if(ch=="}")c--}} END{print c}' file.php

# 5. Post-/pre-increment inside subscripts or arithmetic (Squiz.Operators.IncrementDecrementUsage)
grep -nE '\[\s*\$[a-zA-Z_][a-zA-Z0-9_]*(\+\+|--)' includes/**/*.php

# 6. '$x += 1' / '$x -= 1' (sniffer demands ++$x / --$x)
grep -nE '\$[a-zA-Z_][a-zA-Z0-9_]*\s*[+-]=\s*1\s*;' includes/**/*.php

# 7. Multiple statements per line (Generic.Formatting.DisallowMultipleStatements)
grep -nE ';\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*=' includes/**/*.php

# 8. Single-line function body — opening brace + body on the same line
grep -nE 'function\s+\w+\s*\([^)]*\)\s*\{[^}]+\}\s*$' includes/**/*.php

# 9. Tab+space mixed indentation
grep -nE '^	+ +[A-Za-z<"'"'"']' includes/**/*.php

# 10. $_COOKIE access without sanitize_* (rule: InputNotSanitized)
grep -nE '\$_COOKIE\[' includes/**/*.php | grep -v 'sanitize_'

# 11. $_POST / $_GET access in files that don't reference wp_verify_nonce
for f in $( grep -rlE '\$_(POST|GET)\[' includes/ --include='*.php' ); do
  grep -q 'wp_verify_nonce\|check_admin_referer\|check_ajax_referer\|phpcs:disable.*NonceVerification' "$f" \
    || echo "$f — no nonce check, review required"
done

# 12. i18n calls using double quotes when single quotes would suffice
grep -rnE '(__|esc_html__|esc_attr__|esc_html_e|esc_attr_e|_e|_n|_x|_ex|_nx)\(\s*"[^'"'"'"$\\]*"' includes/ --include='*.php'

# 13a. Single-line phpcs:ignore that only lists ONE NonceVerification rule
grep -rnE 'phpcs:ignore.*NonceVerification\.(Missing|Recommended)([^,]|$)' includes/ --include='*.php' \
  | grep -v 'NonceVerification\.Missing,WordPress\.Security\.NonceVerification\.Recommended\|NonceVerification\.Recommended,WordPress\.Security\.NonceVerification\.Missing'

# 13b. Block-form phpcs:disable for NonceVerification — known unreliable
grep -rnE 'phpcs:(disable|enable).*NonceVerification' includes/ --include='*.php'

# 14. Docblock long description starts with lowercase
grep -rnB1 -E '^\s*\*\s+[a-z][a-z0-9 ]*:' includes/ --include='*.php' \
  | grep -B1 -A1 '^\s*\*\s*$' | head -40

# 15. Bare `echo $var;` lines without an escape function
grep -rnE '^\s*echo\s+\$[a-zA-Z_][a-zA-Z0-9_]*\s*;' includes/ --include='*.php' \
  | grep -v 'esc_\|wp_kses_\|absint\|intval\|(int)\|(string)'

# 16. Wrong i18n text domain. The ONLY valid domain is customer-email-verification-for-woocommerce.
#     Must return ZERO. Plugin Check reports each hit as an ERROR (TextDomainMismatch).
#
#     16a. Allowlist form — catches ANY domain that is not the plugin slug, including
#          typos a blocklist would miss (e.g. 'customer-emial-verification-for-woocommerce').
#          PREFER THIS ONE. Excludes assets/zui/ (read-only library, see the Plugin Check
#          section above for how its 'zorem-ui' domain is handled).
grep -rhoE "(__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e|_n|_x|_ex|_nx)\(\s*[^)]*,\s*'[a-z0-9-]+'\s*[,)]" \
  --include='*.php' . | grep -oE "'[a-z0-9-]+'\s*[,)]$" | sort -u \
  | grep -v "^'customer-email-verification-for-woocommerce'"

#     16b. Blocklist form — the specific wrong domains that have shipped here before.
grep -rnE "(__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e|_n|_x|_ex|_nx)\(\s*[^,]*,\s*'(customer-email-verification|customer-email-verification-pro|customer-emial-verification-for-woocommerce|zorem-ui|react-customizer-framework|shipping-workshop|woocommerce)'\s*[,)]" includes/ templates/ --include='*.php'

# 17. Hardcoded user-facing JS string literals (not read from a localized object)
grep -rnE "(\.text\(|\.html\(|alert\(|showError[A-Za-z]*\(|Message\s*=\s*)'[A-Z][a-z]+ [^']*'" assets/js/ --include='*.js' \
  | grep -v '||'

# 18. Raw $_POST / $_GET / $_COOKIE / $_REQUEST passed directly into nonce funcs
grep -rnE '(wp_verify_nonce|check_admin_referer|check_ajax_referer)\(\s*\$_(POST|GET|COOKIE|REQUEST)\[' \
  includes/ --include='*.php'

# 19. `} elseif` / `} else` split across a newline
grep -rnPzo '}\s*\n\s*(//[^\n]*\n\s*)?(elseif|else)\b' includes/ --include='*.php'

# 20. Superglobal nested INSIDE an `if (...)` condition on the same line
grep -rnE '\b(if|elseif)\s*\([^)]*\$_(POST|GET|REQUEST|COOKIE)\[' \
  includes/ --include='*.php'

# 21. Password read missing the InputNotSanitized suppression
grep -rnE '\$_POST\[.password.\]' includes/ --include='*.php' | \
  grep -v 'InputNotSanitized'

# 21b. Same UNSANITIZED superglobal read twice on one line
grep -rnE '\$_(POST|GET|REQUEST|COOKIE)\[[^]]+\][^$]*wp_unslash\(\s*\$_(POST|GET|REQUEST|COOKIE)\[[^]]+\]\s*\)' \
  includes/ --include='*.php' | grep -v 'sanitize_\|wc_clean\|absint\|intval\|preg_replace'

# 22. Bare WP-core globals reassigned in template code
grep -rnE '^\s*\$(post|posts|pages|page|paged|pagename|wp_query|wp_rewrite|current_user|current_site|m|w|year|monthnum|day|hour|minute|second|id|tag|cat|comment|multipage|more|numpages)\s*=' \
  includes/views/ includes/customizer/ --include='*.php'

# 23. Trailing same-line `// phpcs:ignore ... EscapeOutput.OutputNotEscaped` comments
grep -rnE '<\?php\s+echo\s+[^;]+;\s*//\s*phpcs:ignore.*EscapeOutput' \
  includes/ templates/ --include='*.php'

# 24. Alternative-syntax `<?php ... : ?><body><?php endif; ?>` crammed on one line
grep -rnE '<\?php\s+(if|elseif|else|foreach|for|while|switch)[^?]*:\s*\?>[^<]*<' \
  includes/ templates/ --include='*.php'

# 25. Embedded ternary inside `echo`
grep -rnE '<\?php\s+echo\s+\([^?]+\?\s*esc_(html|attr)__\(.*:\s*esc_(html|attr)__' \
  includes/ templates/ --include='*.php'

# 26. `$type` reassigned in template/view code (WP-global override)
grep -rnE '^\s*\$type\s*=' \
  includes/views/ includes/customizer/ --include='*.php'

# 27. ⚠️ FATAL — `?>` embedded inside a `//` line comment
#     PHP terminates the `//` comment AND the PHP block at the first `?>`.
#     Must return ZERO. To document template syntax in a comment, omit the
#     tags entirely or use a /* */ block comment.
grep -rnE '^\s*//.*\?>' includes/ templates/ --include='*.php'


# 28. i18n $text argument that is not a literal string, and missing $domain.
#     Must return TOTAL: 0. Catches the esc_html_e( $var ) / __( 4 ) / __( $option )
#     family that Plugin Check reports as NonSingularStringLiteralText and
#     MissingArgDomain. A plain grep cannot do this — it needs real arg parsing,
#     so keep this python scanner alongside the repo.
python - <<'PY'
import io, os, re
FUNCS=['__','_e','_x','_ex','_n','_nx','esc_html__','esc_html_e','esc_html_x','esc_attr__','esc_attr_e','esc_attr_x']
NEED={'__':2,'_e':2,'esc_html__':2,'esc_html_e':2,'esc_attr__':2,'esc_attr_e':2,'_x':3,'_ex':3,'esc_html_x':3,'esc_attr_x':3,'_n':4,'_nx':5}
CALL=re.compile(r'\b('+'|'.join(FUNCS)+r')\s*\(')
LIT=re.compile(r'^(\'([^\'\\]|\\.)*\'|"([^"\\$]|\\.)*")$')
def args_of(src,i):
    d=0;out=[];cur=''
    while i<len(src):
        c=src[i]
        if c in '"\'':
            q=c;cur+=c;i+=1
            while i<len(src):
                if src[i]=='\\': cur+=src[i:i+2];i+=2;continue
                cur+=src[i]
                if src[i]==q: break
                i+=1
            i+=1;continue
        if c=='(':
            d+=1
            if d==1: i+=1;continue
        elif c==')':
            d-=1
            if d==0: out.append(cur);return [a.strip() for a in out]
        elif c==',' and d==1: out.append(cur);cur='';i+=1;continue
        cur+=c;i+=1
    return None
n=0
for root,dirs,files in os.walk('.'):
    dirs[:]=[d for d in dirs if d not in ('.git','node_modules','lang')]
    for f in files:
        if not f.endswith('.php'): continue
        p=os.path.join(root,f)
        src=io.open(p,encoding='utf-8',errors='replace').read()
        for m in CALL.finditer(src):
            if m.start() and src[m.start()-1] in '>:$_' or (m.start() and src[m.start()-1].isalnum()): continue
            a=args_of(src,m.end()-1)
            if a is None: continue
            bad=[]
            if not a or not LIT.match(a[0]): bad.append('non-literal $text')
            if len(a)<NEED[m.group(1)]: bad.append('missing $domain')
            if bad:
                n+=1
                print('%s:%d %s() %s'%(p,src.count(chr(10),0,m.start())+1,m.group(1),', '.join(bad)))
print('TOTAL: %d'%n)
PY

# 29. `echo esc_html_e(` / `echo _e(` — the echoing form already echoes.
grep -rnE 'echo\s+(esc_html_e|esc_attr_e|_e|_ex|_nx)\s*\(' includes/ templates/ --include='*.php'

# 31. wp_redirect() instead of wp_safe_redirect() (WordPress.Security.SafeRedirect)
grep -rnE "[^_]wp_redirect\(" includes/ templates/ --include='*.php'

# 30. i18n calls with a printf placeholder but no /* translators: */ comment
#     directly above (WordPress.WP.I18n.MissingTranslatorsComment).
grep -rnE "(__|_e|_n|_x|_nx|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(.*%(\$?[0-9]+\$)?[sd]"   includes/ templates/ --include='*.php' -B1 | grep -A1 -v 'translators:'

# 32. cev_ hooks whose phpcs:ignore is missing or not on the line directly above, and
#     multi-line apply_filters()/do_action() calls with the hook name on its own line
#     (WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound). Must print nothing.
#     Files with a file-level `phpcs:disable ... NonPrefixedHooknameFound` are skipped.
for f in $(grep -rlE "(apply_filters|do_action)\(" includes/ templates/ *.php --include='*.php' 2>/dev/null); do
  grep -q 'phpcs:disable[^\n]*NonPrefixedHooknameFound' "$f" && continue
  awk -v F="$f" '
    /(apply_filters|do_action)\(\s*$/ { print F ":" NR ": hook name on its own line — put it on the apply_filters()/do_action() line"; }
    /(apply_filters|do_action)\(\s*["\x27]cev_/ && prev !~ /NonPrefixedHooknameFound/ { print F ":" NR ": cev_ hook without phpcs:ignore on the line directly above"; }
    { prev = $0 }
  ' "$f"
done
```

**Other release pre-flight:**
- All new strings wrapped in `__()` / `esc_html__()` etc. with text domain `customer-email-verification-for-woocommerce`
- All JS-side strings exposed via `wp_localize_script` and read through a localized object

## Release zips — "give me zip file"

Whenever the user asks for the zip file(s), build **fresh** zips from the
current working folders and save them in this project folder
(`D:\Zorem Project\customer-email-verification-for-woocommerce\`), **overwriting** any older zip with the same name:

- `D:\Zorem Project\customer-email-verification-for-woocommerce\customer-email-verification-for-woocommerce.zip`

Rules:
- The zip root is the plugin folder (`<plugin-folder>/...`) so it installs
  directly in WordPress.
- **Exclude:** `.git/`, `.gitignore`, `.gitattributes`, `.claude/`,
  `node_modules/`, every `*.md` file (incl. `CLAUDE.md`, `README.md`,
  `CHANGELOG.md`) any nested `*.zip`, and `phpcs.xml.dist` / `phpcs.xml`.
- **Do NOT ship `phpcs.xml.dist` / `phpcs.xml`** — Plugin Check rejects them
  as `application_detected` ("Application files are not permitted.", ERROR).
  They stay in the git repo for local PHPCS runs; only the zip excludes them.
- Before zipping, run this file's pre-release scans (if any) and lint changed
  PHP files with WAMP PHP (`/c/wamp64/bin/php/php8.2.29/php.exe -l <file>`).
- Report each zip's full path, file count and size, and say whether the
  folders had uncommitted git changes.
- If a new plugin folder is added to this project, add it to `PLUGINS` below.

```bash
cd "/d/Zorem Project/customer-email-verification-for-woocommerce" && python - <<'EOF'
import os, zipfile
PLUGINS    = ['.']
EXCL_DIRS  = {'.git', '.claude', 'node_modules'}
EXCL_FILES = {'.gitignore', '.gitattributes', 'phpcs.xml.dist', 'phpcs.xml'}
for rel in PLUGINS:
    src  = os.path.abspath(rel)
    name = os.path.basename(src)
    out  = os.path.abspath(name + '.zip')
    n = 0
    with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
        for dp, dn, fn in os.walk(src):
            dn[:] = sorted(d for d in dn if d not in EXCL_DIRS)
            for f in sorted(fn):
                if f in EXCL_FILES or f.lower().endswith(('.md', '.zip')):
                    continue
                p = os.path.join(dp, f)
                z.write(p, (name + '/' + os.path.relpath(p, src)).replace(os.sep, '/'))
                n += 1
    print(out, n, 'files', round(os.path.getsize(out) / 1048576, 2), 'MB')
EOF
```
