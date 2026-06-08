<?php
// Generate simple icon PNGs for certificate info bar
$icons = [
    'icon-student' => ['S', '#1e3a8a', 'Student'],
    'icon-cert'    => ['C', '#1e3a8a', 'Certificate'],
    'icon-date'    => ['D', '#1e3a8a', 'Date'],
    'icon-course'  => ['R', '#f26522', 'Course'],
];

$outDir = __DIR__ . '/../public/assets/images/cert-icons/';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

foreach ($icons as $filename => $data) {
    [$letter, $color, $label] = $data;
    
    $size = 80;
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    
    // Parse hex color
    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));
    $bgColor = imagecolorallocate($img, $r, $g, $b);
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Draw circle
    imagefilledellipse($img, $size/2, $size/2, $size-4, $size-4, $bgColor);
    
    // Draw letter
    $fontSize = 36;
    $bbox = imagettfbbox($fontSize, 0, __DIR__ . '/../public/assets/fonts/cert/GreatVibes-Regular.ttf', $letter);
    $x = ($size - ($bbox[2] - $bbox[0])) / 2 - $bbox[0];
    $y = ($size - ($bbox[1] - $bbox[7])) / 2 - $bbox[7];
    imagettftext($img, $fontSize, 0, (int)$x, (int)$y, $white, __DIR__ . '/../public/assets/fonts/cert/GreatVibes-Regular.ttf', $letter);
    
    imagepng($img, $outDir . $filename . '.png');
    imagedestroy($img);
    echo "Generated: $filename.png\n";
}
echo "Done!\n";
