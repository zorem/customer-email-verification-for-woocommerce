# Draft Spec: Header

> **Status:** DRAFT — review only. Not implemented in AST PRO. The final
> ZUI naming will be settled after Tabs, Sidebar, Settings Layout, Cards
> and Form Rows have also been reviewed. Only then will AST PRO be
> migrated as the reference implementation.
>
> **Rule:** the current AST PRO visual output must remain 100% identical
> when the migration eventually ships.

---

## Current AST PRO header

**File:** `ast-pro/includes/settings/header.php`
**CSS:** `ast-pro/assets/css/ast-settings.css` lines 90-229 + 624-650 + 1649+

```
<header class="ast-set-header" id="ast-set-header">
  └─ <div class="ast-set-header__bar">                       ← brand-bar wrapper
       ├─ <div class="ast-set-header__left">                 ← left cluster wrapper
       │    ├─ <button class="ast-set-header__menu-btn">     ← drawer toggle
       │    └─ <div class="ast-set-logo">                    ← brand cluster
       │         ├─ <span class="ast-set-logo__emblem">A<span class="ast-set-logo__dot"/></span>
       │         ├─ <span class="ast-set-logo__word">AST</span>
       │         ├─ <span class="ast-set-logo__badge">PRO</span>
       │         └─ <span class="ast-set-logo__sub">Fulfillment Manager</span>
       └─ <div class="ast-set-header__right">                ← right cluster wrapper
            ├─ <a class="ast-set-header__docs">[icon] Docs Portal</a>
            └─ <button class="ast-set-header__bell">[bell]</button>
  └─ <nav class="ast-set-nav"> … </nav>                      ← OUT OF SCOPE for header refactor
```

## Problems identified

1. `ast-set-*` prefix is plugin-specific and carries misleading "settings" infix
2. Position-based wrappers `__left` / `__right` are direction-coupled
3. One-off classes `__docs`, `__bell` instead of a generic action concept
4. `.ast-set-logo` is BEM-nested under header but is conceptually a standalone component
5. Sub-name vocabulary is short / inconsistent (`__word`, `__sub`, `__dot`)
6. Redundant single-purpose wrapper `__bar`
7. `__dot` is at the same hierarchical level as `__word`/`__badge`/`__sub` but is a child of `__emblem`

## Proposed final shared structure (target)

| Component | Selector |
|---|---|
| Page header band | `.zui-header` |
| Brand mark | `.zui-brand` |
| Action group | `.zui-header-actions` containing `.zui-header-action` |

```
<header class="zui-header">

  ── menu toggle (single, leading) ──────────────────
  <button class="zui-header__menu-toggle"> [menu] </button>

  ── brand component (independent, reusable) ────────
  <div class="zui-brand">
    <span class="zui-brand__emblem">A<span class="zui-brand__emblem-dot"/></span>
    <span class="zui-brand__name">AST</span>
    <span class="zui-brand__badge">PRO</span>
    <span class="zui-brand__tagline">Fulfillment Manager</span>
  </div>

  ── actions component (0..n) ───────────────────────
  <div class="zui-header-actions">
    <a class="zui-header-action zui-header-action--with-label" href="…">
      [icon] <span class="zui-header-action__label">Docs Portal</span>
    </a>
    <button class="zui-header-action zui-header-action--icon-only">
      [bell]
    </button>
  </div>

  ── existing tabs nav (OUT OF SCOPE — unchanged) ───
  <nav class="ast-set-nav"> … </nav>

</header>
```

## Old → New class rename map

| Old | New |
|---|---|
| `ast-set-header__bar` | *(wrapper removed; layout merges onto `.zui-header`)* |
| `ast-set-header__left` | *(wrapper removed; flex on `.zui-header` handles it)* |
| `ast-set-header` | `zui-header` |
| `ast-set-header__right` | `zui-header-actions` |
| `ast-set-header__menu-btn` | `zui-header__menu-toggle` |
| `ast-set-header__docs` | `zui-header-action zui-header-action--with-label` |
| `ast-set-header__docs-label` | `zui-header-action__label` |
| `ast-set-header__bell` | `zui-header-action zui-header-action--icon-only` |
| `ast-set-header-btn` (existing utility line 624) | `zui-header-action` |
| `ast-set-logo` | `zui-brand` |
| `ast-set-logo__emblem` | `zui-brand__emblem` |
| `ast-set-logo__dot` | `zui-brand__emblem-dot` |
| `ast-set-logo__word` | `zui-brand__name` |
| `ast-set-logo__badge` | `zui-brand__badge` |
| `ast-set-logo__sub` | `zui-brand__tagline` |

## Preserved verbatim (functional contract)

- `<header>` element (banner landmark)
- `id="ast-set-header"` (JS hook in ast-settings.js)
- `data-ast-drawer-toggle` (drawer JS handler)
- `ast_pro_settings_icon( … )` PHP icon helper calls
- Docs link `href` / `target` / `rel`
- All PHP variables, tab loop, AJAX behavior
- `<nav class="ast-set-nav">` and all children (out of scope)

## Visual-identity guarantee

When eventually applied, this rename ships HTML + a mechanical CSS find/replace
in `ast-settings.css`. All numeric values (gap, padding, margins, font-sizes,
shadows, radii) transfer 1:1 to the new selectors. Two wrappers collapse onto
`.zui-header` itself with flex properties consolidating; `.zui-header-actions`
gets `margin-inline-start: auto` to reproduce the previous `__right` cluster
position. Visual output is byte-identical.

## Future cross-plugin slots to consider (raised, not finalized)

When ALP / CBR / CEV / RETURN / SMS headers are audited, the contract may need
to handle:
- Plugins without drawer toggle
- Plugins with two badges (e.g. "PRO" + "BETA")
- Plugins with a back button on the left (sub-page context)
- Plugins with a status indicator in the header (e.g. "Synchronized · 19:16:20")
- Plugins with a custom right-side CTA (e.g. "Upgrade")

The current proposal handles all of these via:
- `.zui-header__menu-toggle` is optional
- `.zui-brand__badge` can repeat
- New leading element (e.g. `.zui-header__back-btn`) can sit before brand
- A status pill can become a new sub-component slotted in `.zui-header-actions`
- Generic `.zui-header-action--with-label` handles CTA links

But these need to be confirmed against actual ALP/CBR/etc markup before
finalizing the contract.

## Approval status

- ✅ Analysis accepted
- ✅ Future ZUI direction accepted
- ✅ Saved as draft spec
- ❌ NOT applied to AST PRO HTML
- ❌ NOT applied to AST PRO CSS
- ❌ NOT touched any wrapper

Next: review Tabs, then Sidebar, then Settings Layout, then Cards, then
Form Rows — then revisit this Header spec with cross-component insight
before finalizing the ZUI naming.
