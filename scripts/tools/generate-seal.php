<?php
/**
 * Generate certificate seal/badge image using PHP GD
 * Output: public/assets/images/certificate-seal.png
 */

$outputPath = __DIR__ . '/../../public/assets/images/certificate-seal.png';
$width = 240;
$height = 280;

$img = imagecreatetruecolor($width, $height);
imagealphablending($img, false);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);

// Colors
$darkBlue = imagecolorallocate($img, 30, 74, 138);   // #1E4A8A
$gold = imagecolorallocate($img, 201, 162, 39);       // #C9A227
$lightGold = imagecolorallocate($img, 246, 183, 69);  // #F6B745
$orange = imagecolorallocate($img, 242, 101, 34);     // #F26522
$white = imagecolorallocate($img, 255, 255, 255);
$lightBlue = imagecolorallocate($img, 46, 112, 218);  // #2E70DA

$centerX = $width / 2;
$centerY = $height / 2 - 10;
$outerRadius = 95;
$innerRadius = 82;

// Outer scalloped border (draw multiple small circles around)
$scallopCount = 24;
for ($i = 0; $i < $scallopCount; $i++) {
    $angle = deg2rad($i * (360 / $scallopCount));
    $sx = $centerX + cos($angle) * ($outerRadius - 6);
    $sy = $centerY + sin($angle) * ($outerRadius - 6);
    imagefilledellipse($img, (int)$sx, (int)$sy, 18, 18, $gold);
}

// Main outer circle (dark blue)
imagefilledellipse($img, (int)$centerX, (int)$centerY, $outerRadius * 2, $outerRadius * 2, $darkBlue);

// Gold ring border
imagesetthickness($img, 3);
imageellipse($img, (int)$centerX, (int)$centerY, $outerRadius * 2 - 10, $outerRadius * 2 - 10, $gold);
imagesetthickness($img, 1);
imageellipse($img, (int)$centerX, (int)$centerY, $outerRadius * 2 - 18, $outerRadius * 2 - 18, $gold);

// Inner lighter circle
imagefilledellipse($img, (int)$centerX, (int)$centerY, $innerRadius * 2, $innerRadius * 2, $lightBlue);
imageellipse($img, (int)$centerX, (int)$centerY, $innerRadius * 2, $innerRadius * 2, $gold);

// Stars at top, left, right
function drawStar($img, $x, $y, $r, $color) {
    $points = [];
    for ($i = 0; $i < 10; $i++) {
        $angle = deg2rad($i * 36 - 90);
        $radius = ($i % 2 === 0) ? $r : $r * 0.4;
        $points[] = $x + cos($angle) * $radius;
        $points[] = $y + sin($angle) * $radius;
    }
    imagefilledpolygon($img, $points, count($points) / 2, $color);
}

drawStar($img, (int)$centerX, (int)$centerY - 55, 10, $gold);
drawStar($img, (int)$centerX - 55, (int)$centerY + 15, 8, $gold);
drawStar($img, (int)$centerX + 55, (int)$centerY + 15, 8, $gold);

// Draw simplified laurel wreath (arcs of small ellipses)
$leafColor = $gold;
$wreathRadius = 58;
$leafCount = 14;
for ($side = -1; $side <= 1; $side += 2) {
    for ($i = 0; $i < $leafCount; $i++) {
        $angle = deg2rad(180 + $side * (20 + $i * (140 / $leafCount)));
        $lx = $centerX + cos($angle) * $wreathRadius;
        $ly = $centerY + sin($angle) * $wreathRadius;
        imagefilledellipse($img, (int)$lx, (int)$ly, 10, 6, $leafColor);
    }
}

// Central text "SEAL" (replaced with star for cleaner look, but let's add text)
// Draw central star instead of text for cleaner look
// (Text rendering without TTF fonts is pixelated)
drawStar($img, (int)$centerX, (int)$centerY, 16, $gold);
imageellipse($img, (int)$centerX, (int)$centerY, 20, 20, $gold);

// Orange ribbon at bottom
$ribbonTop = (int)$centerY + $outerRadius - 15;
$ribbonWidth = 80;
$ribbonHeight = 28;
$ribbonLeft = (int)$centerX - $ribbonWidth / 2;

// Ribbon body
$ribbonPoints = [
    $ribbonLeft, $ribbonTop,
    $ribbonLeft + $ribbonWidth, $ribbonTop,
    $ribbonLeft + $ribbonWidth, $ribbonTop + $ribbonHeight,
    $ribbonLeft + $ribbonWidth - 10, $ribbonTop + $ribbonHeight - 8,
    $ribbonLeft + $ribbonWidth / 2, $ribbonTop + $ribbonHeight,
    $ribbonLeft + 10, $ribbonTop + $ribbonHeight - 8,
    $ribbonLeft, $ribbonTop + $ribbonHeight,
];
imagefilledpolygon($img, $ribbonPoints, count($ribbonPoints) / 2, $orange);

// Ribbon tails (left and right hanging down)
$tailPointsLeft = [
    $ribbonLeft, $ribbonTop + $ribbonHeight,
    $ribbonLeft + 10, $ribbonTop + $ribbonHeight - 8,
    $ribbonLeft + 8, $ribbonTop + $ribbonHeight + 18,
    $ribbonLeft - 2, $ribbonTop + $ribbonHeight + 12,
];
imagefilledpolygon($img, $tailPointsLeft, count($tailPointsLeft) / 2, $orange);

$tailPointsRight = [
    $ribbonLeft + $ribbonWidth, $ribbonTop + $ribbonHeight,
    $ribbonLeft + $ribbonWidth - 10, $ribbonTop + $ribbonHeight - 8,
    $ribbonLeft + $ribbonWidth - 8, $ribbonTop + $ribbonHeight + 18,
    $ribbonLeft + $ribbonWidth + 2, $ribbonTop + $ribbonHeight + 12,
];
imagefilledpolygon($img, $tailPointsRight, count($tailPointsRight) / 2, $orange);

// Save PNG
imagepng($img, $outputPath);
imagedestroy($img);

echo "Seal image generated: {$outputPath}\n";
echo "Dimensions: {$width}x{$height}\n";
