<?php
/**
 * Compress/resize the certificate seal source image for PDF use
 */

$source = __DIR__ . '/../../public/assets/images/certificate-seal-source.png';
$target = __DIR__ . '/../../public/assets/images/certificate-seal.png';

if (!file_exists($source)) {
    echo "Source image not found: {$source}\n";
    exit(1);
}

$info = getimagesize($source);
echo "Source: {$info[0]}x{$info[1]} px, " . round(filesize($source) / 1024) . " KB\n";

// Target dimensions: seal is displayed at ~70pt (~25mm) in PDF.
// 150 DPI => ~150 px per inch => ~90px per 25mm is minimum.
// We want 2x for retina clarity => ~180px is enough. Use 250px to be safe.
$targetWidth = 250;
$targetHeight = (int) round($info[1] * ($targetWidth / $info[0]));

$srcImg = imagecreatefrompng($source);
if (!$srcImg) {
    echo "Failed to load source PNG.\n";
    exit(1);
}

// Preserve transparency
imagealphablending($srcImg, true);

$dstImg = imagecreatetruecolor($targetWidth, $targetHeight);
imagealphablending($dstImg, false);
imagesavealpha($dstImg, true);
$transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
imagefill($dstImg, 0, 0, $transparent);

imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetWidth, $targetHeight, $info[0], $info[1]);

// Try indexed color with 128 colors to reduce file size
if (function_exists('imagetruecolortopalette')) {
    imagetruecolortopalette($dstImg, false, 128);
}

// Save with compression level 9 (maximum PNG compression)
imagepng($dstImg, $target, 9);

imagedestroy($srcImg);
imagedestroy($dstImg);

$finalSize = filesize($target);
echo "Target: {$targetWidth}x{$targetHeight} px, " . round($finalSize / 1024) . " KB\n";
echo "Saved to: {$target}\n";
