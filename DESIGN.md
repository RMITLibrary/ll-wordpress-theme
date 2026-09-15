---
name: RMIT Learning Lab — Library Design System
description: The RMIT Library's SASS design system for the Learning Lab, built on Bootstrap 5.3.3.
colors:
  rmit-blue: "#000054"
  blue-hover: "#0051A8"
  rmit-red: "#E61E2A"
  correct-green: "#008057"
  dark-grey: "#333333"
  interface-grey: "#BFBFBF"
  underlay-grey: "#F5F5F5"
  underlay-selected: "#E6E6E6"
  body-bg: "#FFFFFF"
  body-color-dark: "#DFE2E6"
  body-bg-dark: "#222529"
  link-color-dark: "#6FB7FF"
  link-hover-dark: "#98C3FA"
  accent-dark: "#E6636A"
  underlay-grey-dark: "#1C1F21"
  underlay-selected-dark: "#15171A"
typography:
  display:
    fontFamily: "Museo700, serif"
    fontSize: "2rem"
    fontWeight: 700
    lineHeight: "1.2"
  headline:
    fontFamily: "Museo700, serif"
    fontSize: "1.625rem"
    fontWeight: 700
  title:
    fontFamily: "Museo700, serif"
    fontSize: "1.4375rem"
    fontWeight: 700
  body:
    fontFamily: "Helvetica Neue, Arial, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: "1.5"
  lead:
    fontFamily: "Helvetica Neue, Arial, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 500
  label:
    fontFamily: "Helvetica Neue, Arial, system-ui, sans-serif"
    fontSize: "0.9375rem"
    letterSpacing: "0.02rem"
rounded:
  control: "0.25rem"
  flat: "0px"
spacing:
  xs: "1rem"
  sm: "1.5rem"
  md: "2rem"
  lg: "2.5rem"
  xl: "3rem"
  xxl: "4rem"
components:
  button-primary:
    backgroundColor: "{colors.rmit-blue}"
    textColor: "{colors.body-bg}"
    rounded: "{rounded.control}"
    padding: "0.5rem 1rem"
    typography: "{typography.body}"
  button-primary-hover:
    backgroundColor: "{colors.blue-hover}"
  button-outline:
    backgroundColor: "{colors.body-bg}"
    textColor: "{colors.rmit-blue}"
    rounded: "{rounded.control}"
    padding: "8px 1rem"
  accordion-item:
    backgroundColor: "{colors.body-bg}"
    rounded: "{rounded.flat}"
    padding: "1rem 0"
---

# Design System: RMIT Learning Lab — Library Design System

## Overview

**Creative North Star: "The Library Design System"**

Not a metaphor — the actual name. This is the RMIT Library's own SASS system, layered over
Bootstrap 5.3.3 via the Picostrap5 parent theme, and it behaves like library infrastructure
rather than a brand expression: it is the substrate a thousand pages of discipline content
sit on, and its job is to stay out of their way.

The system is almost entirely structural. Colour is carried by RMIT's institutional palette
rather than a designed one, depth is refused outright, and the only ornament in the whole
system is a serif heading face against a sans body. Everything else — spacing, borders,
tints — is doing legibility work. The result reads as considered rather than styled, which
is correct for a resource a student opens mid-assignment at 11pm.

It is built for two contexts at once. Pages render standalone on the site and embedded in a
Canvas course shell with the chrome stripped, so no component may depend on site furniture
being present around it.

**Key Characteristics:**
- Flat by conviction — tonal underlays and 1px borders, never shadows
- Institutional colour, not chosen colour: RMIT blue and red carry the whole palette
- A single serif (Museo700) for headings; system sans for everything else
- Full light and dark modes through Bootstrap's `color-mode()`
- A 6-step spacing scale that changes value at the `md` breakpoint, not just the layout
- Focus is the one place the system raises its voice

## Colors

The palette is RMIT's institutional one, used with deliberate scarcity: a near-black navy
carries every heading and link, and the red appears almost nowhere.

### Primary
- **RMIT Blue** (`#000054`): Headings, links, primary buttons. A navy so dark it reads as
  authority rather than colour. Mapped onto Bootstrap's `$blue` and `$link-color`.
- **Interaction Blue** (`#0051A8`): Link and button hover only. The one point in the system
  where blue becomes legibly blue.

### Secondary
- **RMIT Red** (`#E61E2A`): The accent. Reserved for genuine emphasis; it is the
  institution's signal colour and loses its meaning if spread.

### Tertiary
- **Correct Green** (`#008057`): Semantic only — correct answers and success states in
  interactive content. Never decorative.

### Neutral
- **Dark Grey** (`#333333`): Body text. Not black — softened for long-form reading.
- **Interface Grey** (`#BFBFBF`): Borders, rules and dividers. Carries all the separation
  work that shadows would do elsewhere. Also mapped over Bootstrap's `$gray-300/400/600`.
- **Underlay Grey** (`#F5F5F5`) and **Underlay Selected** (`#E6E6E6`): Tonal layering for
  panels and selected states — the system's substitute for elevation.

### Dark Mode
A full parallel set: paper `#DFE2E6` on ground `#222529`, links lifted to `#6FB7FF`
(hover `#98C3FA`), accent softened to `#E6636A`, underlays dropped to `#1C1F21` and
`#15171A`. Applied through Bootstrap's `color-mode(dark)`, with a user-facing
`.theme-switch` control.

### Named Rules
**The Institutional Colour Rule.** The palette is RMIT's, not ours. New colours are not
introduced to solve a design problem; the problem is solved with space, weight or a border.

**The Scarce Red Rule.** `#E61E2A` is an accent, not a brand wash. If red appears more than
once in a viewport, it has stopped meaning anything.

## Typography

**Display Font:** Museo700 (with serif fallback)
**Body Font:** Helvetica Neue / Arial / system-ui stack

**Character:** A single deliberate contrast — a warm slab-ish serif for every heading level
against a neutral system sans for reading. The serif signals teaching rather than marketing;
the sans is invisible on purpose. There is no third voice.

### Hierarchy
- **Display / h1** (Museo700, 2rem): One per page. The resource's title.
- **Headline / h2** (Museo700, 1.625rem): Section breaks within a resource.
- **Title / h3** (Museo700, 1.4375rem): Sub-sections; also h4 at 1.25rem.
- **Body** (system sans, 1rem rising to 1.125rem at `md`, line-height 1.5): All prose.
  `text-wrap: pretty` is applied to paragraphs.
- **Lead** (system sans, 1.125rem → 1.25rem at `md`, weight 500): Resource introductions.
- **Label / small** (system sans, 0.8125rem → 0.9375rem at `md`, letter-spacing 0.02rem):
  Captions and metadata. The letter-spacing is there for legibility at small size, not style.

### Named Rules
**The Two Voices Rule.** Museo700 for headings, the system sans for everything else. A third
typeface is a bug.

**The Responsive Type Rule.** Body type steps up at `md` rather than scaling fluidly —
1rem on phones, 1.125rem above. Headings get `text-wrap: balance`, prose gets
`text-wrap: pretty`.

## Layout

Bootstrap's 12-column grid and container behaviour, with content constrained for reading
rather than filling the viewport. The distinctive part is the spacing system: a six-step
scale (`xs` 1rem through `xxl` 4rem) applied via a `do-space()` mixin that emits *different
values per breakpoint* — below `md`, `lg` collapses to 2rem and `xl` to 3rem, so vertical
rhythm tightens on small screens instead of letting a phone inherit desktop air.

Spacing is applied through that mixin rather than Bootstrap utility classes, so the
breakpoint behaviour comes for free and stays consistent.

### Named Rules
**The do-space Rule.** Vertical rhythm goes through `@include do-space(property, step)`, not
`mt-4`. Hard-coded margins bypass the responsive scale.

## Elevation & Depth

**There are no shadows in this system.** Depth is conveyed entirely by tonal layering —
`#F5F5F5` and `#E6E6E6` underlays — and 1px `#BFBFBF` borders. This is a deliberate position,
confirmed rather than inherited.

The single exception is focus, where a 4px ring is an intentional interruption.

### Shadow Vocabulary
- **Focus ring** (`box-shadow: 0 0 0 .25rem rgba(13, 110, 253, 0.4)`; dark mode
  `rgba(111, 183, 255, 0.6)`): The only shadow in the system. Applied on `:focus-within`,
  with `outline-width: 0`.

### Named Rules
**The Flat Rule.** Surfaces are flat. A drop shadow to separate two things means the border
or the spacing isn't doing its job.

**The Focus-Is-The-Exception Rule.** The focus ring is the one place the system uses depth,
and it is suppressed on `:focus` and `:active` so it fires on `:focus-within` only —
keyboard users get it, mouse users don't get a flash on every click.

## Shapes

Two radii and nothing between them. Interactive controls — buttons, inputs — carry a small
`0.25rem` radius. Structural content, most visibly the accordion, is explicitly squared off
(`border-radius: 0 !important` overriding Bootstrap). Separation is drawn with horizontal
1px rules rather than enclosing boxes: the accordion has a top and bottom border with the
left and right borders removed, so items read as entries in a list rather than stacked cards.

### Named Rules
**The Controls-Only Radius Rule.** Rounding marks something as interactive. Content
containers stay square.

## Components

### Buttons
- **Shape:** Small radius (`0.25rem`) on every variant.
- **Primary:** RMIT Blue ground, white text, `0.5rem 1rem` padding, body type,
  `1rem` right margin so buttons in a row never touch.
- **Hover:** Ground shifts to Interaction Blue (`#0051A8`). No transform, no shadow.
- **Outline / default:** Transparent ground, 1px border and text in the link colour, `8px 1rem`
  padding. On hover the label underlines and the colour shifts; the ground stays put.
- **Focus:** 4px focus ring via `default-focus`, dark-mode aware.

### Links
- **Default:** Underlined, inheriting RMIT Blue. `overflow-wrap: break-word` so long URLs
  in academic content can't break the layout.
- **Hover:** Underline is *removed* — the inverse of the usual convention, and consistent
  across the system.
- **Feature links** (cards, tiles): start undecorated and gain an underline on hover.

### Accordion
The signature component — used heavily for transcripts and progressive disclosure.
- **Shape:** Squared (`border-radius: 0 !important`).
- **Borders:** 1px `#BFBFBF` top and bottom only; sides removed.
- **Padding:** `1rem 0` on the header, `1.5rem 0 0` on the body — vertical only, so content
  stays aligned with the prose column.
- **IDs:** Derived from the heading slug plus an occurrence counter, stable across renders.

### Breadcrumbs
- **Separator:** `/` between items, `<` as the mobile back affordance.
- **Colour:** Body colour rather than link colour — navigation that doesn't compete with
  content. Dark-mode aware.

### Forms / Inputs
- **Focus:** Same 4px ring as buttons, via the shared `default-focus` mixin.
- **Select indicator:** Recoloured to the link colour in dark mode.

### Theme Switcher
A visible `.theme-switch` control with a legend styled as an h5 — light/dark is a user
choice, not only a system preference.

## Do's and Don'ts

### Do:
- **Do** apply vertical spacing with `@include do-space(property, step)` so it responds at the
  `md` breakpoint.
- **Do** use `@include default-focus` on anything focusable; it handles the dark-mode ring and
  the `:focus-within` behaviour.
- **Do** separate content with a 1px `#BFBFBF` rule or an `#F5F5F5` underlay.
- **Do** keep the `0.25rem` radius for controls and square corners for content.
- **Do** build every component to survive with the page chrome stripped — any resource may be
  embedded in a Canvas iframe.
- **Do** define both light and dark values through `color-mode(dark)` when adding colour.

### Don't:
- **Don't** add a `box-shadow` for anything but focus.
- **Don't** introduce a colour outside the RMIT palette to solve a hierarchy problem — use
  space, weight, or a border.
- **Don't** add a third typeface. Museo700 for headings, system sans for everything else.
- **Don't** round content containers; the squared accordion is the reference.
- **Don't** use Bootstrap spacing utilities in place of `do-space` — they don't step down on
  small screens.
- **Don't** rely on `:focus` alone; the system deliberately suppresses it in favour of
  `:focus-within`.
