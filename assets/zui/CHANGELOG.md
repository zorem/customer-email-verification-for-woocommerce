# Changelog

All notable changes to this library are documented here.
Versioning follows [SemVer](https://semver.org/):

- **Patch** (0.0.x) — bug fix, no markup change
- **Minor** (0.x.0) — new component or additive class
- **Major** (x.0.0) — breaking markup or class rename

---

## [1.9.2] — 2026-07-02

### BREAKING — PHP functions moved to `Zorem\UI` namespace

Every consumer plugin must update its call sites in the same release that
pulls this version — there are **no backward-compat shims** (any global
shim would still trigger `WordPress.NamingConventions.PrefixAllGlobals`
in consumer PHPCS runs, which is exactly the problem this migration is
fixing).

| Before (global)                       | After (namespaced)                          |
| ------------------------------------- | ------------------------------------------- |
| `zui_icon( $name )`                   | `\Zorem\UI\icon( $name )`                   |
| `zui_get_icon( $name )`               | `\Zorem\UI\get_icon( $name )`               |
| `zui_get_plugin_brand( $slug )`       | `\Zorem\UI\get_plugin_brand( $slug )`       |
| `zui_get_ecosystem_plugins( $slug )`  | `\Zorem\UI\get_ecosystem_plugins( $slug )`  |

Arguments and return shapes are unchanged. Recommended consumer-plugin
search-and-replace:

```
zui_icon(                    → \Zorem\UI\icon(
zui_get_icon(                → \Zorem\UI\get_icon(
zui_get_plugin_brand(        → \Zorem\UI\get_plugin_brand(
zui_get_ecosystem_plugins(   → \Zorem\UI\get_ecosystem_plugins(
```

### Why namespace

Every consumer plugin's PHPCS run (SMS, CEV, AST PRO, ALP, CBR, SRE)
flagged the library's four `zui_*` global functions with
`WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound`
because `zui` isn't in the consumer's declared prefix allowlist. Adding
`zui` to each consumer's allowlist is a legitimate workaround but
requires touching every consumer plugin's `phpcs.xml.dist`. Namespacing
the library satisfies PHPCS's `PrefixAllGlobals` rule automatically
(it only fires on the global namespace) with **zero** consumer PHPCS
config changes.

### Changed

- `brand.php`, `eco-plugins.php`, `icons.php` now declare
  `namespace Zorem\UI;`. Internal cross-references
  (`zui_icon()` → `get_icon()` inside icons.php) updated accordingly.
- All `function_exists()` guards now use
  `__NAMESPACE__ . '\\<function>'` so double-inclusion still works
  across multi-plugin installs.
- Docblocks in all three PHP files carry a "Since 1.9.2" note.

### Docs

- Every reference to `zui_icon()`, `zui_get_icon()`,
  `zui_get_plugin_brand()`, `zui_get_ecosystem_plugins()` in
  `ALL-ZUI-IMPLEMENTATION-GUIDE.md` and `ZUI-COMPONENTS-PREVIEW.html`
  updated to the namespaced form. Each helper's section now carries a
  "Migrating from 1.x" note with the exact search-and-replace pattern.

### Not changed

- CSS component library (no class renames — pure PHP refactor).
- JS bundle (`js/zui.js`) — no changes.
- Function return shapes are byte-identical.

### Also fixed — PHPCS `EscapeOutput.OutputNotEscaped` on `icons.php`

`\Zorem\UI\icon()` (the "echo the SVG" helper) previously suppressed
`WordPress.Security.EscapeOutput.OutputNotEscaped` with a trailing
`// phpcs:ignore` comment. Some consumer plugin PHPCS configs don't
respect end-of-line ignore comments, so the sniff still fired. Replaced
the ignore with a real `wp_kses()` call using a new library-internal
`\Zorem\UI\svg_kses_allowed()` allowlist covering every element +
attribute the icon resolver emits (`svg`, `circle`, `ellipse`, `path`,
`polygon`, `polyline`, `line`, `rect`). The SVG source is a hardcoded
resolver so `wp_kses()` is purely defence-in-depth, but the switch
means consumer PHPCS runs pass without any suppression comment.

### Fixed

- **Ecosystem registry — PHPCS `TextDomainMismatch` in every consumer
  plugin** (`eco-plugins.php`): the library previously wrapped nine
  strings (six plugin descriptions, two badges, one "Active in this
  store" stat) in `__( '…', 'zorem-ui' )`. Because the library is
  bundled inside plugins whose own text domains are
  `sms-for-woocommerce`, `customer-email-verification-for-woocommerce`,
  `ast-pro`, etc., PHPCS flagged every one of those nine calls in every
  consumer. Removed all `__()` calls from `eco-plugins.php` — the
  function now returns raw English strings. Consumers render them as-is
  (English fallback) or maintain their own local `__()` map keyed off
  the library's English strings.

### Contract change (see guide)

- `zui_get_ecosystem_plugins()` used to return **partially localised**
  strings (localised on sites that shipped a `zorem-ui.mo` file,
  English everywhere else). It now returns **raw English strings only**.
  Consumer plugins that already just echoed the returned values continue
  to work unchanged. Consumer plugins that relied on a bundled
  `zorem-ui.mo` to translate the strings need to either (a) accept
  English or (b) build a small local translation map — the pattern is
  documented in "String translation (since 1.9.2)" in
  `ALL-ZUI-IMPLEMENTATION-GUIDE.md`.

- `brand.php` is unaffected — it never called `__()` in the first place.
  Taglines like "Fulfillment Manager" and "Local Pickup Manager" have
  always been raw English strings.

### Docs

- Added "String translation (since 1.9.2)" subsection under the Ecosystem
  registry docs in `ALL-ZUI-IMPLEMENTATION-GUIDE.md` — explains the i18n
  contract, shows the recommended English-fallback pattern, shows the
  optional per-plugin translation map, and lists what to avoid
  (`__( $var, 'domain' )` triggers `NonSingularStringLiteralText`;
  `// phpcs:ignore` comments are discouraged).

---

## [1.9.1] — 2026-07-02

### Added

- **Consent / Opt-in Modal pattern** — three small helper classes added
  to `css/components/modal.css` so plugins can render the canonical
  "Thank you for installing …" / usage-tracking opt-in dialog with
  no new component file. Helpers:
  `.zui-modal__dialog--wide` (widens the dialog to 600 px);
  `.zui-modal__prose` (long-form paragraph stack inside the body —
  14 px gap between `<p>`, primary-color underlined `<a>` links);
  `.zui-modal__optin` + `.zui-modal__optin-label` (divided
  checkbox cluster + intro label). The split footer (primary
  action LEFT, "Skip" RIGHT) works with the existing
  `.zui-modal__foot` — its default `justify-content: space-between`
  handles the layout when the buttons are placed as direct
  children of the footer (skip the `.zui-modal__actions` wrapper
  for this pattern).
- **Sidebar — dropped `align-self: stretch`** (`css/components/sidebar.css`):
  removed the stretch rule that forced the sidebar to match the
  content column's height even when the sidebar's own content was
  much shorter.
- **Accordion — body padding fix** (`css/components/accordion.css`):
  changed `.zui-accordion__body` padding from `0 16px 16px` to
  `16px` so plugin-owned body content always sits with 16 px around
  it and never jams against the divider above.
- **Implementation guide section** for the Consent / Opt-in Modal
  pattern in `ALL-ZUI-IMPLEMENTATION-GUIDE.md` — canonical HTML,
  helper-class reference, split-footer contract, PHP snippet for
  "open once on activation", common mistakes.
- **Preview entry** in `ZUI-COMPONENTS-PREVIEW.html` — live inline
  render of the consent modal with the exact "Thank you for
  installing the SMS for WooCommerce plugin" copy, both checkboxes,
  and the "Allow & Continue" / "Skip" footer, plus an HTML snippet.
  New "Consent / Opt-in Modal" link added to the Modals TOC group
  just after "Modal".

---

## [1.9.0] — 2026-07-02

### Added

- **Accordion component** (`css/components/accordion.css`) — vertical
  stack of self-contained collapsible cards. Each item pairs a clickable
  `<button>` head (label slot on the left, optional right-aligned
  controls + rotating chevron) with a hidden body that expands on click.
  Content-agnostic: heads accept plain text, a `.zui-badge`, or an
  icon + label; bodies accept form rows, textareas, merge tags, or any
  other markup. Multi-open by default. Two modifiers:
  `.zui-accordion--single` (one-open-at-a-time) and
  `.zui-accordion--flush` (no card chrome, items sit flat with a
  bottom divider). States: `.is-open` (body visible, chevron rotated
  180°, focus ring) and `.is-disabled` (opacity 0.72 — used when a
  per-item toggle is off). Phase 1 — registered in `css/zui.css` under
  a new "Collapsibles" section. Promoted from the SMS plugin's local
  `.smswoo-notif-row` pattern so every Zorem plugin renders the pattern
  identically.
- **JS auto-wiring** in `js/zui.js` — new `_wireAccordions()` scanner
  delegates clicks on `.zui-accordion__head` to toggle `.is-open` on
  the parent item, flip `body.hidden`, and set `aria-expanded`. Clicks
  that land inside a `.zui-toggle` / `<input>` / `<select>` /
  `<textarea>` / `[data-zui-accordion-ignore]` are ignored so nested
  form controls stay independent. Items with
  `[data-zui-accordion-default-open]` are opened on init. Emits
  `zui:accordiontoggle` CustomEvent (bubbles, `detail: { open }`).
  Public API: `ZUI.accordion.open( item )` / `.close( item )` /
  `.toggle( item )`.
- **`.zui-badge__dot`** helper added to `css/components/badge.css` —
  6 px filled circle inline element rendered in the badge's current
  text color. Placed inside a `.zui-badge` to produce the
  "• Pending payment" pill pattern used by the accordion for
  order-status heads.
- **Implementation guide section** for `zui-accordion` in
  `ALL-ZUI-IMPLEMENTATION-GUIDE.md` — purpose, required DOM hierarchy,
  full class / modifier / state / data-attribute tables, auto-wiring
  contract, programmatic API, BEM tree, expected visual result, common
  mistakes, correct/incorrect code examples, and a full SMS plugin
  migration table mapping every `.smswoo-notif-row*` class to its
  `.zui-accordion*` equivalent (plus a note that the SMS plugin's
  hand-rolled expand/collapse JS in `sms_notifications.phtml` can be
  deleted entirely).
- **Preview entry** in `ZUI-COMPONENTS-PREVIEW.html` — two live demos
  (5-row status-badge accordion with inline toggles matching the SMS
  Order Status screen, and a 3-row FAQ-style plain-text accordion with
  `.zui-accordion--single`), plus HTML and JS snippets. New
  "Collapsibles" TOC group added to the preview sidebar between
  "Pro Upsell" and "Auto-wiring".

---

## [1.8.4] — 2026-06-30

### Added

- **PRO Feature Lock component** (`css/components/pro-feature.css`) —
  inline indicator placed inside a `.zui-row__control` next to a disabled
  toggle/checkbox to flag a PRO-only feature. Three siblings: disabled
  control + solid-blue `.zui-pro-feature__badge` pill + soft-tinted
  `.zui-pro-feature__lock` 28 × 28 px chip with a lock icon. Promoted
  from AST-local `.ast-pro-feature-row` / `.ast-pro-feature-lock` styles
  so every Zorem plugin renders the indicator identically. Phase 1 —
  registered in the `zui.css` aggregator under "Inline info helpers"
  alongside `tipbox.css` and `lock-section.css`. Self-contained — no
  `badge.css` dependency.
- **Lock Section badge self-contained** (`css/components/lock-section.css`):
  added `.zui-lock-section__badge` rule with the blue-pill style that
  was previously only defined inside AST's `ast-settings.css`. The
  optional "PRO" badge inside `.zui-lock-section__title` now renders
  correctly anywhere the library is loaded — no AST-local styles
  needed and no dependency on `badge.css`.
- **Implementation guide sections** for `zui-pro-feature` and the
  updated `zui-lock-section__badge` in `ALL-ZUI-IMPLEMENTATION-GUIDE.md`.
- **Preview entries** in `ZUI-COMPONENTS-PREVIEW.html` — live demo +
  HTML snippet for `.zui-pro-feature` (disabled toggle + PRO badge +
  lock chip) and updated Lock Section preview to use the new
  `.zui-lock-section__badge` class. Both entries added to the preview
  sidebar nav.

### Fixed

- **Primary button — anchor underline still visible** (`css/components/button.css`):
  `.zui-btn-primary` was missing `text-decoration: none`, so when used
  on an `<a>` (Upgrade CTAs, Lock Section CTA, Upsell CTA) the default
  anchor underline bled through. Added the rule so primary buttons
  always render without an underline regardless of element type.
- **Preview HTML missing `lock-section.css` link**
  (`ZUI-COMPONENTS-PREVIEW.html`): the per-component `<link>` list in
  the preview's `<head>` never got a stylesheet entry for
  `lock-section.css` when the component was first added in v1.8.3, so
  the Lock Section live preview rendered unstyled. Added the missing
  link tag alongside `tipbox.css`.

---

## [1.8.3] — 2026-06-30

### Added

- **Lock Section component** (`css/components/lock-section.css`) — centered
  PRO-upgrade promo card used at the top of a PRO-only section. Renders an
  icon emblem, a title with inline PRO badge, a description paragraph, and
  a primary CTA anchor; the fields below can then be rendered in a
  read-only / disabled state so users still see what they'd get while
  being directed to the upgrade CTA. Promoted from an AST-local component
  to a shared library component (Phase 1 — registered in `css/zui.css`
  aggregator under "Inline info helpers" alongside `tipbox.css`).
- **`lock` icon** added to `icons.php` (Lucide outline padlock) — used by
  `.zui-lock-section__icon` and any other lock-themed UI.
- **Implementation guide section** for `zui-lock-section` in
  `ALL-ZUI-IMPLEMENTATION-GUIDE.md` — purpose, required DOM hierarchy,
  full class reference, optional `__preview` sibling pattern for dimming
  the gated field area, element-level details, expected visual result,
  common mistakes and correct/incorrect examples. Inserted right after the
  Tipbox section so it lives alongside the other "Inline info helpers".
- **Preview entry** in `ZUI-COMPONENTS-PREVIEW.html` — live demo of a
  Lock Section with the canonical lock icon, an inline PRO badge, a
  description and a primary CTA, plus an HTML snippet showing both the
  banner and the optional `__preview` sibling. New "Lock Section" link
  added to the preview sidebar between Tipbox and Tooltip.

---

## [1.8.2] — 2026-06-29

### Fixed

- **Upsell Panel — feature list row gap too tight** (`css/components/upsell.css`):
  Increased `gap` row value from `10px` to `16px` so the feature checklist
  matches the visual spacing of the reference CEV design.

---

## [1.8.0] — 2026-06-29

### Added

- **Pro Upsell Panel component** (`css/components/upsell.css`) — full-width
  promotional section for displaying on the Settings page of a free plugin
  to upsell the PRO version. Contains a branded header (52 px icon emblem
  with primary blue gradient + eyebrow pill + headline + description), a
  responsive 3-column feature checklist (`<ul>` / `<li>`) with optional
  inline "NEW" badges, and a footer with an optional coupon offer block
  (yellow dashed chip) and a primary blue gradient CTA anchor. Phase 2
  (deferred — must be loaded directly via `<link>` or `wp_enqueue_style`;
  not imported by `css/zui.css`). No JavaScript required.
  Modifier: `.zui-upsell--cols-2` for 2-column feature layouts.
  Built-in responsive breakpoints: 3 cols → 2 cols at 960 px → 1 col at
  640 px with full-width stacked footer.
- **Implementation guide section** for `zui-upsell` in
  `ALL-ZUI-IMPLEMENTATION-GUIDE.md` — purpose, required DOM hierarchy,
  full class reference table, modifier table, element-level details, CSS
  load order instructions, expected visual result, common mistakes,
  correct/incorrect code examples, and a full CEV plugin migration table
  mapping every `cev-pro-promo__*` class to its `zui-upsell__*` equivalent.
- **Preview entry** in `ZUI-COMPONENTS-PREVIEW.html` — live demo with all
  19 CEV PRO features (including NEW badges), a coupon code, and the CTA
  button; plus HTML and PHP code snippets. New "Pro Upsell" TOC group added
  to the preview sidebar.

---

## [1.7.0] — 2026-06-23

### Added

- **Calendar component** (`css/components/calendar.css`) — new month-view
  date picker primitive. Renders inline (filter cards, report pages) or
  inside a popover floating off a date input. Supports single-date
  selection, range selection (continuous band between two endpoints
  via `.zui-calendar--range`), today highlight, muted adjacent-month
  cells, disabled cells, and an optional footer of quick-select preset
  chips. Companion `.zui-datepicker` wrapper composes the calendar with
  a `.zui-input` text field + a calendar icon at the inset-inline-end.
  Registered in the aggregator (`css/zui.css`) alongside the other form
  controls. The library ships the visual layer only; consumer plugins
  own the month renderer, selection state, and popover toggle JS (auto-
  init hook: `data-zui-datepicker`).
- **Implementation guide section** for Calendar in
  `ALL-ZUI-IMPLEMENTATION-GUIDE.md` (purpose, required structure for
  both inline and popover forms, full modifier matrix, expected visual
  result, common mistakes, and migration notes). Calendar added to the
  Form Controls branch of the Component Dependency Map.
- **Preview examples** in `ZUI-COMPONENTS-PREVIEW.html` — three live
  previews (single-date inline, range variant with disabled tail cells,
  and date-picker input shown in both closed and open states) plus
  three HTML snippets and a JS-contract snippet.

---

## [1.6.2] — 2026-06-22

### Fixed

- **License Save Button — black slate background diverged from primary blue**
  (`css/components/lic-inactive.css`): `.zui-lic-savebtn` had a hard-coded
  `background: #0f172a` (and `#1e293b` hover) which read as a different button
  family than every other save button in the plugin. Switched to
  `var(--zui-primary)` / `var(--zui-primary-hover)` so all save buttons share
  one token-driven blue. Lifecycle classes (`.is-saving`, `.is-saved`)
  unchanged.
- **Docs — `.zui-lic-savebtn` was undocumented**: added a "License Save
  Button" section to the Implementation Guide and a live preview block (idle
  / `.is-saving` / `.is-saved`) to the Components Preview, plus a JS snippet
  showing the canonical `ZUI.btnSaving` → `ZUI.btnSaved` → `ZUI.snackbar`
  lifecycle. No markup change for consumers.

### Fixed (previously)

- **Multiselect widget — class name mismatch** (`js/zui-app.js`): JS was
  creating chip/option/placeholder/check DOM nodes with the old `ast-set-ms__*`
  prefix instead of the library's `zui-ms__*` prefix, so the CSS in
  `multiselect.css` never matched the dynamically-rendered elements. All six
  affected class names corrected:
  `ast-set-ms__chip` → `zui-ms__chip`,
  `ast-set-ms__chip-label` → `zui-ms__chip-label`,
  `ast-set-ms__chip-remove` → `zui-ms__chip-remove`,
  `ast-set-ms__placeholder` → `zui-ms__placeholder`,
  `ast-set-ms__option` → `zui-ms__option`,
  `ast-set-ms__check` → `zui-ms__check`.
  No markup changes — consumers require no template updates.
- **Multiselect widget — `×` button vertical alignment** (`css/components/multiselect.css`):
  Added `display: inline-flex; align-items: center` to `.zui-ms__chip-remove` so the
  `×` glyph (`font-size: 13px`) is perfectly centred within the button box regardless of
  the chip label font size (`11px`). Previously rendered as `inline-block` which caused a
  1–2 px visual offset in some browsers.
- **Multiselect widget — widget never initialized** (`js/zui-app.js`): `initMultiselect`
  queried `zui-set-ms__native / control / chips / dropdown` (wrong prefix) so the guard
  `!select || !control || …` always returned early — click handlers and dropdown were
  never wired up. Fixed all four selectors to `zui-ms__*`. Also fixed two
  `.zui-set-section-header` references → `.zui-section-header`.

---

## [1.6.1] — 2026-06-17

### Added

- **`ZUI.syncSidebarToggles( tab )`** — hides any element marked with
  `data-zui-sidebar-tabs` when the active tab slug isn't in its allowed list.
  Use for the header hamburger / menu-toggle on plugins where only some tabs
  render a sidebar; the toggle is useless on tabs without one.
- **`data-zui-sidebar-tabs="<comma,separated,slugs>"`** — declarative
  attribute pattern. Place on any element (typically
  `.zui-header__menu-toggle`); the library shows/hides it automatically.
- Auto-fires on `zui:tabswapped` document events. Plugins whose tab
  mechanism emits a different event name (e.g. `ast:tabswapped`) can call
  `ZUI.syncSidebarToggles( tab )` from their own swap handler.

---

## [1.6.0] — 2026-06-17 (plugin chrome registry)

New library-level registries so the per-plugin chrome (header brand + License
ecosystem grid) is sourced from a single place across every Zorem consumer
plugin. No CSS / JS / markup changes; purely additive PHP APIs.

### New APIs

- **`zui_get_plugin_brand( $slug )`** — returns the header brand cluster
  (`name` / `badge` / `tagline` / `icon` / `emblem_bg` / `emblem_color`)
  for the given plugin basename. Returns `null` for unknown slugs so
  consumers can fall back. Registered plugins: AST PRO, ALP, CBR, CEV,
  SMS, SRE. New file: `brand.php`.
- **`zui_get_ecosystem_plugins( $current_slug = '' )`** — returns the
  6-plugin list rendered in every License-tab ecosystem grid. Logo paths
  are pre-resolved to absolute URLs under the library's `images/eco/`
  folder. Passing the caller's basename auto-hides its own entry so a
  plugin never advertises itself. New file: `eco-plugins.php`.

### New assets

- `images/eco/trackship.png` — TrackShip ecosystem-card logo, previously
  shipped by AST PRO. Now owned by the library.

### Consumer migration

A consumer plugin that previously hardcoded its brand markup or its
`$zui_ecosystem_plugins` array should:

1. `require_once <plugin>/assets/zui/brand.php;` and use
   `zui_get_plugin_brand( plugin_basename( $main_file ) )`.
2. `require_once <plugin>/assets/zui/eco-plugins.php;` and use
   `zui_get_ecosystem_plugins( plugin_basename( $main_file ) )`.

Brand fallback is mandatory at the call site (use `is_array()`) so an
unregistered plugin still renders.

---

## [1.5.2] — 2026-06-17 (documentation completeness pass)

No CSS / JS / markup changes — this release **only updates the two doc files**
so consumer-plugin developers have full coverage of what's already shipping.
Safe to skip the sync to existing plugins; pull only if you want the new docs.

### Implementation guide (`ALL-ZUI-IMPLEMENTATION-GUIDE.md`)

- **Fixed critical typo:** `data-modal-close` → `data-zui-modal-close` (11
  occurrences across the modal docs). Code copied from the previous version
  would silently fail to close modals.
- **Added App-Level Wiring & Data Attributes** master section covering every
  `data-zui-*` attribute the library auto-wires: tab / section / drawer /
  modal / slideout / dropzone / filter-form / sort. Each with a copy-pasteable
  HTML snippet and a list of CustomEvents emitted.
- **Added Phase 1 vs. Phase 2 callout** at the top of the Component Reference
  table — clarifies which components ship enabled in `css/zui.css` and which
  10 components are documented but their `@import` is commented out until a
  consumer plugin opts in (badge, card-grid, color-input, filter-bar, list-row,
  merge-tags, note, pagination, table, textarea).
- **Added full `.zui-actions` component spec** (kebab row-actions menu —
  toggle + dropdown, auto-wired by `js/zui.js`). Was previously shipping
  without any doc coverage.
- **Added full `.zui-slideout` component spec** (right-edge side drawer —
  the contextual counterpart to `.zui-modal`). Documents the
  `data-zui-slideout-toggle` / `data-zui-slideout-close` wiring and the
  `ZUI.slideout.open()` / `.close()` programmatic API.
- **Updated icon section** with `zui_icon( $name )` / `zui_get_icon( $name )`
  PHP helper docs, all 42 available icon names grouped by intent, fallback
  behaviour (unknown name → `sliders`), and how to add a new icon.

### Preview HTML (`ZUI-COMPONENTS-PREVIEW.html`)

- **New `#cmp-actions` tile** — live preview of the kebab menu with HTML snippet.
- **New `#cmp-slideout` tile** — static slideout panel rendered statically so
  developers can see the chrome without opening it; full HTML + wiring snippet
  including the programmatic API.
- **New visual icon grid** — all 42 icons rendered as a hover-titled grid with
  the name label below each tile, plus a PHP-helper code panel.
- **Expanded `#cmp-wire-attrs` reference** — added modal, slideout, dropzone,
  filter-form, and sortable-table attributes (was previously tab/section/drawer
  only). Each with a one-line behaviour summary + emitted events.
- **Sidebar TOC entries** for the two new component tiles.
- Footer version bumped to 1.5.2.

---

## [1.5.1] — 2026-06-17

### Changed

- `lic-page.css` — `.zui-lic-head` bottom border now uses `--zui-border` (`#e2e8f0`)
  instead of `--zui-divider` (`#f1f5f9`). The divider token was nearly invisible on the
  `--zui-bg` page background; the slightly darker border token gives the section
  separator a visible (but still subtle) treatment.

### Synced from AST PRO (drift fixes)

- `js/zui-app.js` — carriers 3-dot menu now queries `.zui-menu__list` (was the legacy
  `.zui-set-carriers-menu__list`), and the CSV-import sub-tabs query
  `.zui-segmented__item` (was `.zui-set-csv-subtab`). Markup contracts already used the
  new class names; the JS was lagging.

## [1.0.0] — 2026-06-08 (AST PRO rebase)

Full rebase of the library against the AST PRO implementation. AST PRO is now
the single source of truth. The earlier 0.2.0 spec was authored before the
AST PRO migration completed; this release replaces it with components extracted
from the actual AST PRO contracts.

### BREAKING — class renames + DOM contract changes

| 0.2.0 (old) | 1.0.0 (new) | Notes |
|---|---|---|
| `.zui-pageheader` + `-left/-right/-brand/-title/-subtitle` | `.zui-header` + `__bar/__lead/__menu-toggle` + `.zui-brand` + `.zui-header-actions` + `.zui-header-action--with-label/--icon-only` | Full BEM rewrite |
| `.zui-tab` (single) | `.zui-tabs__item` + `.zui-tabs__badge` | BEM child of `.zui-tabs` |
| `.zui-settings-layout` | `.zui-tab-panel` + `.zui-layout` (+ `--full`) + `.zui-content` (+ `--full`) + `.zui-section` | Split into 4 primitives |
| `.zui-sidebar-item` | `.zui-sidebar__item` + `__icon/__label/__badge/__mobile-head/__close/__overlay` + `.zui-sidebar-open` state | Full BEM + mobile drawer |
| `.zui-help-card` | `.zui-quickhelp` + `__title/__text/__links/__link (+--muted)/__art/__paper (+--1/--2)/__sphere/__check` | Renamed + 10 BEM children |
| `.zui-section-header` slot pattern | `.zui-section-header__main/__icon/__text/__titlewrap/__title/__badge/__sub/__actions/__action` | Richer 9-slot structure |
| (split out) | `.zui-savebtn` + `.zui-savebtn-wrap` + `__label/__spinner` + `--inert` | Save button is now its own sub-component |
| `.zui-form-row/form-label/form-control` | `.zui-row` (+ `--inline/--port`) + `__head/__label/__desc/__hint/__control/__notice` | 6 slot children, inline + stacked variants |
| `.zui-multiselect` + `.zui-chip` | `.zui-ms` + `__native/__control/__chips/__chip/__chip-label/__chip-remove/__placeholder/__chevron/__dropdown/__option/__check` | Full custom widget with native `<select>` as save-of-truth |
| `.zui-toggle` (input + sibling label pseudo) | `<label class="zui-toggle">` wrapping `__input + __track + __thumb` spans | New DOM structure (no pseudo trickery) |
| `.zui-radio` (inline) + `.zui-radio-card` | `.zui-radio-cards` (container) + `.zui-radio-card` + `__input/__body/__label/__desc` | Card variant only; inline radio not in AST PRO |
| `.zui-select` (single class) | `.zui-select-wrap` + `.zui-select` + `.zui-select-chevron` | Wrapper + custom chevron |
| `.zui-file-upload` (+ `--dropzone`) | `.zui-upload` + `.zui-upload__btn` | Renamed; dropzone deferred |
| `.zui-btn` + `--primary/--outline/--ghost/--danger/--icon/--sm/--lg` | `.zui-btn-primary` / `.zui-btn-secondary` / `.zui-btn-ghost` / `.zui-btn-block` | Flat single-class variants (AST PRO convention) |
| `.zui-modal-backdrop/-content/-head/-body/-actions/-close` | `.zui-modal` + `__backdrop/__dialog/__head/__head-row/__head--edit/__title/__sub/__close/__body/__foot/__actions/__navbtn/__search/__search-icon/__loading` | Full BEM, 14 slots |
| `.zui-tooltip` with `data-zui-tip` attr | `.zui-tooltip` + explicit `__bubble/__arrow` elements | Rich DOM bubble |
| `.zui-license-status` (+ `--active/--inactive`) | `.zui-lic-card` (slotted) + `.zui-lic-active` + `.zui-lic-inactive` (each with full sub-element set) | Split into 3 state files |
| `.zui-docs-list` | `.zui-lic-help` + `__title/__list` | Renamed for License-page scope |
| `.zui-extension-card` | `.zui-lic-plugin` + 13 children | Rich slot pattern |

### Icon system

- **Removed:** Dashicons dependency (`<span class="dashicons">…</span>`)
- **Added:** `.zui-icon` SVG container — works with inline Lucide-style stroke SVGs emitted by a per-plugin PHP helper. Library ships the CSS contract; SVG path data lives in the consumer plugin.

### Tokens

- Renamed all `--ast-*` tokens to `--zui-*`
- Values **byte-identical** to AST PRO `ast-settings.css` lines 14-44
- Token set: 7 brand colors, 4 surfaces, 4 text colors, 3 chip tints, 3 radii, 3 shadows, 1 font

### Scope

- `.zui-scope` wrapper class (was already used; now the only scope name)
- All rules scoped under `.zui-scope` — does not leak into wp-admin

### Added components (8 new — AST PRO proved cross-plugin need)

- `field.css` — `.zui-field` + `__label/__input` (modal form field)
- `upload.css` — `.zui-upload` + `__btn` (file picker, simplified — no dropzone yet)
- `checkcard.css` — `.zui-checkcard` + `__box/__text` (selection card)
- `tipbox.css` — `.zui-tipbox` + `__head/__text` (inline info box)
- `tab-panel.css` — `.zui-tab-panel` (per-top-tab show/hide unit)
- `content.css` — `.zui-content` (+ `--full`) (main content column)
- `section.css` — `.zui-section` (per-section panel)
- `savebtn.css` — `.zui-savebtn` extracted from section-header
- `icon.css` — `.zui-icon` SVG container
- `quickhelp.css` — `.zui-quickhelp` (replaces help-card.css with richer slot structure)

### Added License page components (7 — fully cross-plugin shared)

- `lic-page.css` — root `.zui-lic` + head + telemetry + grid
- `lic-card.css` — slotted license card (composes `.zui-card`)
- `lic-active.css` — active state + deactivate row
- `lic-inactive.css` — inactive state + telemetry preferences
- `lic-help.css` — docs sidebar (replaces docs-list.css)
- `lic-eco.css` — ecosystem grid header + filters + search
- `lic-plugin.css` — plugin promo card (composes `.zui-card`; replaces extension-card.css)

### Removed (obsolete)

- `page-header.css` — replaced by `header.css`
- `settings-layout.css` — split into `layout.css` + `content.css` + `section.css` + `tab-panel.css`
- `help-card.css` — replaced by `quickhelp.css`
- `file-upload.css` — replaced by `upload.css`
- `license-status.css` — split into `lic-card.css` + `lic-active.css` + `lic-inactive.css`
- `docs-list.css` — replaced by `lic-help.css`
- `extension-card.css` — replaced by `lic-plugin.css`
- `tile.css` — dropped (composes with `.zui-card` + module class; no extra primitive needed)
- `zui-app.css` — temporary scaffold; all CSS now lives in proper component files

### Phase 2 deferred (on-disk, not imported yet — author when consumer ships)

- `badge.css` — standalone badge primitive (currently AST PRO uses parent-owned `__badge` slots)
- `card-grid.css` — responsive grid (AST PRO uses `.zui-lic-eco__grid` instead; promote when 2nd consumer needs it)
- `color-input.css` — color picker (OSM module-owned currently)
- `filter-bar.css` — filter toolbar (CEV + SMS planned)
- `list-row.css` — draggable row (ALP + CBR planned)
- `merge-tags.css` — `{token}` chips (RETURN + SMS planned)
- `note.css` — info/success/warning callout
- `pagination.css` — page nav
- `snackbar.css` — toast notifications
- `table.css` — data table (FD legacy DataTables)
- `textarea.css` — multi-line input (Bulk Paste module-owned)

These ship on demand when their target plugin migrates. The CSS contracts already
exist in this folder — uncomment the `@import` in `zui.css` when ready.

### Library is now compatible with AST PRO

Every selector, every value, every spacing token in this library matches AST PRO's
`assets/css/ast-settings.css` byte-for-byte (modulo scope wrapper + token name).
AST PRO can be rebuilt entirely from this library; ALP / CBR / CEV / SMS / RETURN
adopt by copying markup verbatim and changing only their plugin-specific config.

---

## [0.2.0] — Pre-rebase scaffold

First fully-authored library based on early specs. Superseded by 1.0.0 (above).
See `LIBRARY-AUDIT-vs-AST-PRO.md` for the divergence analysis that drove the 1.0.0
rebase.

### Added
- 33 component CSS files (all rewritten or replaced in 1.0.0)
- Foundation: `tokens.css`, `reset.css`, `zui.css` aggregator
- `js/zui.js` auto-init scanner

### Promoted Phase 2 → Phase 1 at 0.2.0
- `color-input` (used by AST PRO + ALP order status row)
- `file-upload` (used by AST PRO CSV + RETURN blocklist) — renamed to `upload` in 1.0.0
- `filter-bar` (used by SMS Logs + CEV Unverified Users)
