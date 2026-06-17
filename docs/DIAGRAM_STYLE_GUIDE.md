# Edutrack lesson diagram style guide

Canonical spec for all lesson diagrams. Read this + the exemplar
`public/assets/diagrams/_style-reference/mobile-money-flow.svg` before authoring.

## Hard rules
- **Format:** standalone `.svg` files saved under `public/assets/diagrams/<course-slug>/<name>.svg`.
- **No emoji, no external images, no `<style>`, no scripts, no external fonts.**
  Emojis and external refs do NOT render when an SVG is loaded via `<img>`.
- Use only drawn vector shapes (`rect`, `circle`, `path`, `line`, `polygon`) and `<text>`.
- Always set `viewBox` (e.g. `0 0 660 300`). Do NOT set width/height attributes — CSS scales it.
- Set `font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif"` on the root `<svg>`.
- Add `role="img"` and a descriptive `aria-label` on the root `<svg>`, plus a `<title>`.
- Diagrams sit on a WHITE card (the CSS adds it). Do not draw a full-bleed background rect.
- Keep total file small (< ~6 KB). Aim for clarity, not decoration.

## Palette (use these exact hex values)
- Ink / headings: `#0a1628`   • Muted text: `#6b6f78`   • Faint/footnote: `#9aa3af`
- Navy (primary): `#0b4f8c`  on tint `#eef4fb`
- Green (success/positive): `#1f9d57`  on tint `#ecfaf2`
- Amber (action/output): `#f59e0b`  on tint `#fef6e7`
- Red (risk/warning, sparingly): `#dc2626`  on tint `#fdecec`
- Neutral box: fill `#f7f8fa`, stroke `#d8dee6`
- Arrows / connectors: `#9aa3af`, stroke-width 2.5, with a filled triangle head.

## Typography sizes
- Diagram title: 20px bold `#0a1628`, centered near top.
- Subtitle (optional): 13px `#6b6f78`.
- Box heading: 15px bold `#0a1628`. Box body: 12px `#6b6f78`.
- Footnote (optional): 11.5px `#9aa3af` near bottom.

## Approved layout patterns (pick what fits the concept; copy the exemplar's spacing)
1. **Horizontal flow** (3–4 stages + arrows) — processes, "how X works". (See exemplar.)
2. **Comparison** — two side-by-side cards (e.g. navy vs amber) with a heading each and 3–4 bullet lines.
3. **Vertical steps / timeline** — numbered circles down the left, label + note to the right.
4. **Layered stack** — stacked horizontal bands (e.g. hardware → OS → apps; or IoT: devices → gateway → cloud → app).
5. **Cycle** — 3–4 boxes around a loop with curved arrows (e.g. plan → do → check → act).

Rounded rects: `rx="16"` for stage cards, `rx="8"` for small chips. Stroke-width 1.5 (2 for emphasis).

## Quality bar
- Text must never overflow its box — keep labels short, wrap manually onto multiple `<text>` lines.
- Align elements on a tidy grid; equal gaps between stages.
- **Spacing:** leave >=14px between any icon+label cluster and the next; never let
  adjacent labels touch (e.g. three icons in a row need their captions spaced apart,
  not run together like "CallDirectionsVisit").
- **No overlaps:** a nested box must not cover the parent card's title or other text.
  Give nested elements clear margins. When in doubt, make the diagram taller.
- Every diagram must teach something specific to THAT lesson (not generic clip-art).
- Prefer one strong, legible diagram over several cramped ones.

## How to embed in a lesson (in the content seeder HTML)
```html
<figure><img class="lesson-diagram" src="/assets/diagrams/<course-slug>/<name>.svg" alt="<plain description>"><figcaption>Figure: <one-line explanation>.</figcaption></figure>
```
Place it after the lesson's intro paragraph (a lead visual), or beside the section it illustrates.
