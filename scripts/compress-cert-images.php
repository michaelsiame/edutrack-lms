<?php
$dir = __DIR__ . '/../public/assets/images/';

function resizePng($src, $dest, $maxW, $maxH) {
    $srcImg = imagecreatefrompng($src);
    $w = imagesx($srcImg);
    $h = imagesy($srcImg);
    
    $ratio = min($maxW / $w, $maxH / $h, 1.0);
    $newW = (int)($w * $ratio);
    $newH = (int)($h * $ratio);
    
    $dstImg = imagecreatetruecolor($newW, $newH);
    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagepng($dstImg, $dest, 7); // compression level 7
    imagedestroy($srcImg);
    imagedestroy($dstImg);
    
    $oldKb = round(filesize($src) / 1024);
    $newKb = round(filesize($dest) / 1024);
    echo basename($src) . ": {$w}x{$h} ($oldKb KB) → {$newW}x{$newH} ($newKb KB)\n";
}

// Compress images for PDF use
resizePng($dir . 'certificate-seal.png', $dir . 'certificate-seal.png', 200, 300);
resizePng($dir . 'teveta-logo.png', $dir . 'teveta-logo.png', 200, 150);
resizePng($dir . 'logo.png', $dir . 'logo-pdf.png', 150, 162);

echo "Done!\n";
