<?php
$w = 160;
$h = 200;
$img = imagecreatetruecolor($w, $h);
imagealphablending($img, false);
imagesavealpha($img, true);
$trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $trans);

$blue = imagecolorallocate($img, 30, 58, 138);
$gold = imagecolorallocate($img, 201, 162, 39);
$lightGold = imagecolorallocate($img, 216, 158, 46);
$darkGold = imagecolorallocate($img, 184, 133, 31);

$cx = 80;
$cy = 75;

// Outer dashed ring
for ($a = 0; $a < 360; $a += 8) {
    $r = 70;
    $x1 = $cx + $r * cos(deg2rad($a));
    $y1 = $cy + $r * sin(deg2rad($a));
    $x2 = $cx + $r * cos(deg2rad($a + 4));
    $y2 = $cy + $r * sin(deg2rad($a + 4));
    imageline($img, (int)$x1, (int)$y1, (int)$x2, (int)$y2, $gold);
}

// Solid gold ring (drawn as circle)
for ($a = 0; $a < 360; $a += 0.5) {
    $r = 62;
    $x = $cx + $r * cos(deg2rad($a));
    $y = $cy + $r * sin(deg2rad($a));
    imagesetpixel($img, (int)$x, (int)$y, $gold);
}

// Blue filled circle
imagefilledellipse($img, $cx, $cy, 110, 110, $blue);

// Inner gold ring
for ($a = 0; $a < 360; $a += 0.5) {
    $r = 55;
    $x = $cx + $r * cos(deg2rad($a));
    $y = $cy + $r * sin(deg2rad($a));
    imagesetpixel($img, (int)$x, (int)$y, $gold);
}

// 5-point star
$starPoints = [];
$outerR = 30;
$innerR = 12;
for ($i = 0; $i < 5; $i++) {
    $angle = deg2rad($i * 72 - 90);
    $starPoints[] = (int)($cx + $outerR * cos($angle));
    $starPoints[] = (int)($cy + $outerR * sin($angle));
    $angle = deg2rad($i * 72 + 36 - 90);
    $starPoints[] = (int)($cx + $innerR * cos($angle));
    $starPoints[] = (int)($cy + $innerR * sin($angle));
}
imagefilledpolygon($img, $starPoints, $gold);

// Ribbon connector bar
imagefilledrectangle($img, 55, 138, 105, 152, $lightGold);

// Left ribbon tail
imagefilledpolygon($img, [55, 152, 70, 152, 62, 188, 42, 178], $lightGold);
// Right ribbon tail
imagefilledpolygon($img, [105, 152, 90, 152, 98, 188, 118, 178], $lightGold);

// Ribbon shadow lines
imageline($img, 62, 152, 55, 182, $darkGold);
imageline($img, 98, 152, 105, 182, $darkGold);

$path = __DIR__ . '/../public/assets/images/certificate-seal.png';
imagepng($img, $path);
imagedestroy($img);
echo "Seal saved to: $path\n";
echo "Size: " . filesize($path) . " bytes\n";
