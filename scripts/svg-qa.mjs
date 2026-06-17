#!/usr/bin/env node
/* svg-qa.mjs — deterministic quality checks for lesson diagrams.
 *
 * Catches the defects that a blind author can't see:
 *   - text or shapes spilling outside the viewBox (clipped by the canvas edge)
 *   - text overflowing the card/box it sits in (clipped at the box bottom/side)
 *   - overlapping text labels (the "CallDirectionsVisit" crowding problem)
 *   - style-guide violations (emoji, <style>/<script>, external refs, no viewBox,
 *     hardcoded width/height on the root <svg>)
 *
 * Usage:  node scripts/svg-qa.mjs <file.svg> [more.svg ...]
 *         node scripts/svg-qa.mjs $(find public/assets/diagrams -name '[*].svg')
 * Exit:   0 = clean, 1 = issues found (prints actionable messages).
 *
 * Text metrics are estimates (avg glyph ~0.52em wide), so a small tolerance is
 * applied — it flags real clipping/overlap, not hairline rounding.
 */
import { readFileSync } from 'node:fs';

const GLYPH_W = 0.52;          // avg glyph width in em for sans-serif
const ASCENT = 0.80, DESCENT = 0.25;
const PAD = 2;                 // tolerance in px before flagging

function attr(tag, name) {
  const m = tag.match(new RegExp(`\\b${name}\\s*=\\s*"([^"]*)"`));
  return m ? m[1] : null;
}
function num(v) { const n = parseFloat(v); return Number.isFinite(n) ? n : null; }

function textWidth(s, fs) {
  // strip tags/entities for length; entities count as ~1 char
  const len = s.replace(/<[^>]*>/g, '').replace(/&[a-z]+;/gi, 'x').length;
  return len * fs * GLYPH_W;
}

function checkFile(path) {
  const issues = [];
  const svg = readFileSync(path, 'utf8');

  // --- style-guide gates -------------------------------------------------
  const root = (svg.match(/<svg\b[^>]*>/) || [''])[0];
  const vb = attr(root, 'viewBox');
  if (!vb) issues.push('no viewBox on <svg>');
  if (attr(root, 'width') || attr(root, 'height'))
    issues.push('root <svg> has hardcoded width/height (use only viewBox)');
  if (/<style[\s>]/.test(svg)) issues.push('contains <style> (not allowed; img-loaded SVG ignores it)');
  if (/<script[\s>]/.test(svg)) issues.push('contains <script>');
  if (/xlink:href|<image[\s>]/.test(svg)) issues.push('contains external <image>/xlink:href');
  const ext = (svg.match(/https?:\/\/[^"']+/g) || []).filter(u => !u.includes('w3.org'));
  if (ext.length) issues.push(`external URL ref(s): ${ext.slice(0, 2).join(', ')}`);
  // emoji / symbol ranges (rough): anything in common emoji blocks
  if (/[\u{1F000}-\u{1FAFF}\u{2600}-\u{27BF}\u{2190}-\u{21FF}\u{2B00}-\u{2BFF}]/u.test(svg))
    issues.push('contains emoji/symbol glyphs (render inconsistently in <img> SVG — use vector shapes)');

  if (!vb) return issues.map(m => ({ path, msg: m }));
  const [vx, vy, vw, vh] = vb.trim().split(/[\s,]+/).map(Number);

  // --- collect rects (potential containers) ------------------------------
  const rects = [];
  for (const m of svg.matchAll(/<rect\b[^>]*>/g)) {
    const t = m[0];
    const x = num(attr(t, 'x')), y = num(attr(t, 'y'));
    const w = num(attr(t, 'width')), h = num(attr(t, 'height'));
    if ([x, y, w, h].every(v => v !== null)) rects.push({ x, y, w, h });
  }

  // --- collect texts -----------------------------------------------------
  const texts = [];
  for (const m of svg.matchAll(/<text\b([^>]*)>([\s\S]*?)<\/text>/g)) {
    const t = `<text ${m[1]}>`;
    const x = num(attr(t, 'x')), y = num(attr(t, 'y'));
    const fs = num(attr(t, 'font-size')) || 12;
    const anchor = attr(t, 'text-anchor') || 'start';
    const content = m[2].replace(/<[^>]*>/g, '').trim();
    if (x === null || y === null || !content) continue;
    const w = textWidth(content, fs);
    let left = x;
    if (anchor === 'middle') left = x - w / 2;
    else if (anchor === 'end') left = x - w;
    texts.push({
      content, fs,
      box: { l: left, r: left + w, t: y - fs * ASCENT, b: y + fs * DESCENT },
    });
  }

  // --- 1. everything inside the viewBox ----------------------------------
  for (const t of texts) {
    if (t.box.l < vx - PAD || t.box.r > vx + vw + PAD || t.box.t < vy - PAD || t.box.b > vy + vh + PAD)
      issues.push(`text "${t.content.slice(0, 28)}" spills outside the canvas (viewBox ${vw}x${vh})`);
  }
  for (const r of rects) {
    if (r.x < vx - PAD || r.x + r.w > vx + vw + PAD || r.y < vy - PAD || r.y + r.h > vy + vh + PAD)
      issues.push(`a rect (${r.w}x${r.h}) extends past the canvas edge`);
  }

  // --- 2. text fits inside its container box -----------------------------
  for (const t of texts) {
    const cx = (t.box.l + t.box.r) / 2, cy = (t.box.t + t.box.b) / 2;
    // smallest rect whose interior contains the text centre
    let host = null;
    for (const r of rects) {
      if (cx > r.x && cx < r.x + r.w && cy > r.y && cy < r.y + r.h) {
        if (!host || r.w * r.h < host.w * host.h) host = r;
      }
    }
    if (host) {
      if (t.box.b > host.y + host.h + PAD)
        issues.push(`text "${t.content.slice(0, 28)}" is clipped at the BOTTOM of its box (make the box taller or move text up)`);
      if (t.box.l < host.x - PAD || t.box.r > host.x + host.w + PAD)
        issues.push(`text "${t.content.slice(0, 28)}" is wider than its box (shorten it or widen the box)`);
    }
  }

  // --- 3. overlapping text labels ----------------------------------------
  for (let i = 0; i < texts.length; i++) {
    for (let j = i + 1; j < texts.length; j++) {
      const a = texts[i].box, b = texts[j].box;
      const ox = Math.min(a.r, b.r) - Math.max(a.l, b.l);
      const oy = Math.min(a.b, b.b) - Math.max(a.t, b.t);
      if (ox > 3 && oy > 3)
        issues.push(`labels "${texts[i].content.slice(0, 16)}" and "${texts[j].content.slice(0, 16)}" overlap (space them apart)`);
    }
  }

  return issues.map(m => ({ path, msg: m }));
}

const files = process.argv.slice(2);
if (!files.length) { console.error('usage: node scripts/svg-qa.mjs <file.svg> ...'); process.exit(2); }

let all = [];
for (const f of files) {
  try { all = all.concat(checkFile(f)); }
  catch (e) { all.push({ path: f, msg: `could not parse: ${e.message}` }); }
}

if (!all.length) {
  console.log(`✓ ${files.length} SVG(s) passed QA`);
  process.exit(0);
}
const byFile = {};
for (const i of all) (byFile[i.path] ||= []).push(i.msg);
for (const [p, msgs] of Object.entries(byFile)) {
  console.log(`\n✗ ${p}`);
  for (const m of msgs) console.log(`    - ${m}`);
}
console.log(`\n${all.length} issue(s) across ${Object.keys(byFile).length} file(s).`);
process.exit(1);
