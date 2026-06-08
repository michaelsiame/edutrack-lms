<?php
$outDir = __DIR__ . '/../public/assets/images/cert-icons/';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

function createIcon($filename, $drawFunc, $color) {
    global $outDir;
    $size = 120;
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    
    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));
    $col = imagecolorallocate($img, $r, $g, $b);
    
    $drawFunc($img, $size, $col);
    
    imagepng($img, $outDir . $filename);
    imagedestroy($img);
    echo "Generated: $filename\n";
}

function drawStudent($img, $s, $col) {
    imagefilledrectangle($img, $s*0.15, $s*0.55, $s*0.85, $s*0.65, $col);
    $points = [$s*0.15, $s*0.55, $s*0.5, $s*0.35, $s*0.85, $s*0.55];
    imagefilledpolygon($img, $points, 3);
    imageline($img, $s*0.85, $s*0.55, $s*0.85, $s*0.75, $col);
    imagefilledellipse($img, $s*0.85, $s*0.78, $s*0.08, $s*0.08, $col);
}

function drawCert($img, $s, $col) {
    $lineWidth = imagesetthickness($img, 3);
    imagerectangle($img, $s*0.2, $s*0.15, $s*0.8, $s*0.85, $col);
    imagesetthickness($img, 3);
    imageline($img, $s*0.3, $s*0.3, $s*0.7, $s*0.3, $col);
    imageline($img, $s*0.3, $s*0.42, $s*0.7, $s*0.42, $col);
    imageline($img, $s*0.3, $s*0.54, $s*0.7, $s*0.54, $col);
    imageline($img, $s*0.3, $s*0.66, $s*0.55, $s*0.66, $col);
    imagefilledrectangle($img, $s*0.35, $s*0.75, $s*0.45, $s*0.85, $col);
    imagefilledrectangle($img, $s*0.55, $s*0.75, $s*0.65, $s*0.85, $col);
}

function drawDate($img, $s, $col) {
    imagesetthickness($img, 3);
    imagerectangle($img, $s*0.15, $s*0.25, $s*0.85, $s*0.85, $col);
    imageline($img, $s*0.3, $s*0.1, $s*0.3, $s*0.3, $col);
    imageline($img, $s*0.7, $s*0.1, $s*0.7, $s*0.3, $col);
    imageline($img, $s*0.15, $s*0.4, $s*0.85, $s*0.4, $col);
    imageline($img, $s*0.15, $s*0.55, $s*0.85, $s*0.55, $col);
    imageline($img, $s*0.15, $s*0.7, $s*0.85, $s*0.7, $col);
    imageline($img, $s*0.42, $s*0.4, $s*0.42, $s*0.85, $col);
    imageline($img, $s*0.69, $s*0.4, $s*0.69, $s*0.85, $col);
}

function drawCourse($img, $s, $col) {
    imagesetthickness($img, 4);
    imagearc($img, $s*0.5, $s*0.45, $s*0.5, $s*0.5, 0, 180, $col);
    imagearc($img, $s*0.5, $s*0.45, $s*0.5, $s*0.5, 180, 360, $col);
    imagearc($img, $s*0.22, $s*0.4, $s*0.2, $s*0.25, 90, 270, $col);
    imagearc($img, $s*0.78, $s*0.4, $s*0.2, $s*0.25, 270, 90, $col);
    imagefilledrectangle($img, $s*0.45, $s*0.68, $s*0.55, $s*0.82, $col);
    imagefilledrectangle($img, $s*0.35, $s*0.82, $s*0.65, $s*0.88, $col);
}

createIcon('icon-student.png', 'drawStudent', '#1e3a8a');
createIcon('icon-cert.png', 'drawCert', '#1e3a8a');
createIcon('icon-date.png', 'drawDate', '#1e3a8a');
createIcon('icon-course.png', 'drawCourse', '#f26522');
echo "Done!\n";
