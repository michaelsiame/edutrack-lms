<?php
/*
 * One-off generator for the certificate watermark tile.
 * Run with: php scripts/generate_cert_watermark.php
 *
 * Produces a transparent PNG that tiles cleanly via CSS background-repeat.
 * The tile contains two rows of text, the second offset by half the text
 * width, so horizontal+vertical repeat produces a seamless brick pattern.
 */

$tileWidth  = 720;
$tileHeight = 120;
$fontPath   = '/usr/share/fonts/truetype/liberation/LiberationSerif-Italic.ttf';
$fontSize   = 13;
$text       = 'Edutrack Computer Training College';
$outPath    = __DIR__ . '/../public/assets/images/cert-watermark.png';

if (!file_exists($fontPath)) {
    fwrite(STDERR, "Font not found: $fontPath\n");
    exit(1);
}

$im = imagecreatetruecolor($tileWidth, $tileHeight);
imagesavealpha($im, true);
$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $transparent);

// Soft blue-gray, low alpha so it sits behind content without overpowering.
// GD alpha: 0=opaque, 127=transparent. ~110 ≈ 13% opacity.
$ink = imagecolorallocatealpha($im, 30, 58, 138, 110);

$bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
$textWidth = $bbox[2] - $bbox[0];

// One text + a gap roughly equal to text width creates the visible spacing
// between repeats within a single row.
$step = $textWidth + 40;

// Row 1: y baseline at 1/4 tile height
$y1 = (int)($tileHeight * 0.30) + $fontSize;
// Row 2: y baseline at 3/4 tile height, x offset by half a step for brick pattern
$y2 = (int)($tileHeight * 0.80) + $fontSize;
$row2OffsetX = (int)(-$step / 2);

// Draw enough copies to cover the tile width with overflow on both sides so
// horizontal repeat lines up regardless of phase.
for ($x = -$step; $x < $tileWidth + $step; $x += $step) {
    imagettftext($im, $fontSize, 0, $x, $y1, $ink, $fontPath, $text);
    imagettftext($im, $fontSize, 0, $x + $row2OffsetX, $y2, $ink, $fontPath, $text);
}

if (!is_dir(dirname($outPath))) {
    mkdir(dirname($outPath), 0755, true);
}

imagepng($im, $outPath, 9);
imagedestroy($im);

echo "Wrote: $outPath (" . filesize($outPath) . " bytes, {$tileWidth}x{$tileHeight})\n";
