<?php
$inDir = __DIR__ . '/../public/assets/images/cert-icons/';
$tmpDir = __DIR__ . '/../tmp-cert-icons/';
if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

function processIcon($filename, $bgColor, $iconMaxSize = 70) {
    global $inDir, $tmpDir;
    
    $originalBackup = $tmpDir . 'orig_' . $filename;
    if (!file_exists($originalBackup)) {
        copy($inDir . $filename, $originalBackup);
    }
    
    $src = $originalBackup;
    $info = getimagesize($src);
    if (!$info) { echo "Invalid: $filename\n"; return; }
    
    switch ($info[2]) {
        case IMAGETYPE_PNG: $srcImg = imagecreatefrompng($src); break;
        case IMAGETYPE_JPEG: $srcImg = imagecreatefromjpeg($src); break;
        default: echo "Unsupported: $filename\n"; return;
    }
    
    imagealphablending($srcImg, false);
    imagesavealpha($srcImg, true);
    
    $w = imagesx($srcImg);
    $h = imagesy($srcImg);
    
    // Create output canvas
    $size = 120;
    $outImg = imagecreatetruecolor($size, $size);
    imagesavealpha($outImg, true);
    $tr = imagecolorallocatealpha($outImg, 0, 0, 0, 127);
    imagefill($outImg, 0, 0, $tr);
    
    // Parse background color (tint)
    $r = hexdec(substr($bgColor, 1, 2));
    $g = hexdec(substr($bgColor, 3, 2));
    $b = hexdec(substr($bgColor, 5, 2));
    $bg = imagecolorallocate($outImg, $r, $g, $b);
    
    // Draw light circle background
    imagefilledellipse($outImg, $size/2, $size/2, $size-4, $size-4, $bg);
    
    // Calculate resized dimensions
    $ratio = min($iconMaxSize / $w, $iconMaxSize / $h);
    $newW = max(1, (int)($w * $ratio));
    $newH = max(1, (int)($h * $ratio));
    $dstX = ($size - $newW) / 2;
    $dstY = ($size - $newH) / 2;
    
    imagecopyresampled($outImg, $srcImg, (int)$dstX, (int)$dstY, 0, 0, $newW, $newH, $w, $h);
    
    imagepng($outImg, $inDir . $filename);
    imagedestroy($srcImg);
    imagedestroy($outImg);
    
    $newKb = round(filesize($inDir . $filename) / 1024);
    echo "Processed: $filename ($newW x $newH on 120x120 canvas, $newKb KB)\n";
}

processIcon('icon-student.png', '#dbeafe'); // light blue
processIcon('icon-cert.png', '#dbeafe');
processIcon('icon-date.png', '#dbeafe');
processIcon('icon-course.png', '#ffedd5'); // light orange
echo "Done!\n";
