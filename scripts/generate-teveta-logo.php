<?php
$outDir = __DIR__ . '/../public/assets/images/';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$w = 200;
$h = 80;
$img = imagecreatetruecolor($w, $h);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);

$blue = imagecolorallocate($img, 30, 58, 138);
$orange = imagecolorallocate($img, 242, 101, 34);
$white = imagecolorallocate($img, 255, 255, 255);
$darkBlue = imagecolorallocate($img, 20, 40, 100);

$circleX = 35;
$circleY = 40;
$circleR = 28;
imagefilledellipse($img, $circleX, $circleY, $circleR*2, $circleR*2, $blue);
imagefilledellipse($img, $circleX-2, $circleY-2, $circleR*2-4, $circleR*2-4, $darkBlue);

$font = __DIR__ . '/../public/assets/fonts/cert/GreatVibes-Regular.ttf';
$bbox = imagettfbbox(28, 0, $font, '7');
$x = $circleX - ($bbox[2]-$bbox[0])/2 - $bbox[0];
$y = $circleY - ($bbox[1]-$bbox[7])/2 - $bbox[7] + 2;
imagettftext($img, 28, 0, (int)$x, (int)$y, $white, $font, '7');

imagefilledellipse($img, $circleX+18, $circleY-18, 10, 10, $orange);

$fontBold = __DIR__ . '/../public/assets/fonts/cert/GreatVibes-Regular.ttf';
imagettftext($img, 22, 0, 72, 35, $blue, $fontBold, 'TEVETA');
imagettftext($img, 9, 0, 72, 55, $blue, $fontBold, 'COMPUTER EDUCATION');

imagepng($img, $outDir . 'teveta-logo.png');
imagedestroy($img);
echo "Generated: teveta-logo.png\n";
