#!/usr/bin/env bash
# render-svg.sh — render a lesson-diagram SVG to PNG so an agent can SEE it.
# Uses headless Chrome (renders SVG exactly like the browser the students use).
#
# Usage:  scripts/render-svg.sh <file.svg> [out.png]
# Output: prints the PNG path; open/Read it to visually verify the diagram.
set -euo pipefail

CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
[ -x "$CHROME" ] || { echo "Chrome not found at $CHROME"; exit 2; }

svg="${1:?usage: render-svg.sh <file.svg> [out.png]}"
[ -f "$svg" ] || { echo "no such file: $svg"; exit 2; }
out="${2:-${svg%.svg}.preview.png}"

abs="$(cd "$(dirname "$svg")" && pwd)/$(basename "$svg")"
html="$(mktemp /tmp/svgprev.XXXXXX).html"
cat > "$html" <<HTML
<!doctype html><meta charset="utf-8">
<body style="margin:0;background:#ffffff">
<img src="file://$abs" style="width:760px;display:block">
</body>
HTML

"$CHROME" --headless --disable-gpu --hide-scrollbars \
  --screenshot="$out" --window-size=780,560 \
  --default-background-color=FFFFFFFF \
  "file://$html" >/dev/null 2>&1 || true

rm -f "$html" 2>/dev/null || true
[ -f "$out" ] && echo "rendered: $out" || { echo "render failed"; exit 1; }
