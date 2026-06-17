---
name: svg-diagram-qa
description: Verify and fix lesson-diagram SVGs before finishing. Use whenever you author or edit any .svg under public/assets/diagrams/. Catches text clipped at box edges, labels overflowing/colliding, content outside the canvas, and style-guide violations — defects you cannot see while writing SVG blind.
---

# SVG diagram QA — author, then VERIFY

You author SVGs without seeing them, so you miss clipping and crowding. After
writing or editing ANY diagram, you MUST verify it with these two tools and fix
every issue before moving on. Never consider a diagram done until it passes.

## Step 1 — deterministic lint (always)
```
node scripts/svg-qa.mjs public/assets/diagrams/<slug>/<name>.svg
```
It reports issues like:
- `text "…" is clipped at the BOTTOM of its box` → make that box taller, or move
  the text up / reduce its font-size. Box bottom = `y + height`; a 12px label
  needs its baseline `y` at least ~4px above the box bottom.
- `text "…" is wider than its box` → shorten the label, split it onto two
  `<text>` lines, or widen the box.
- `labels "A" and "B" overlap` → space them apart (≥14px gap between clusters).
- `… spills outside the canvas` → move it inward or enlarge the `viewBox`.
- style violations (emoji, `<style>`/`<script>`, external refs, missing viewBox,
  hardcoded root width/height) → remove/fix them.

Re-run after each fix. Iterate until it prints `✓ … passed QA`.

## Step 2 — look at it (final confidence)
Your model can read images. Render and inspect the result:
```
scripts/render-svg.sh public/assets/diagrams/<slug>/<name>.svg /tmp/check.png
```
Then READ `/tmp/check.png`. Confirm visually: title readable, every label fully
inside its box, nothing overlapping, balanced spacing, looks professional and
matches `docs/DIAGRAM_STYLE_GUIDE.md` and the reference SVGs in
`public/assets/diagrams/_style-reference/` and `public/assets/diagrams/digital-marketing/`.
If anything looks off, fix the SVG and repeat from Step 1.

## Rules
- A diagram is "done" only after Step 1 prints PASS and Step 2 looks clean.
- Prefer making boxes a little taller / labels a little shorter over cramming.
- Keep the validated visual style (palette, vector-only, no emoji).
