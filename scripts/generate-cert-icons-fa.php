<?php
$outDir = __DIR__ . '/../public/assets/images/cert-icons/';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$font = __DIR__ . '/../public/assets/fontawesome/webfonts/fa-solid-900.ttf';

function createIcon($filename, $codepoint, $color) {
    global $outDir, $font;
    $size = 160;
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    
    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));
    $col = imagecolorallocate($img, $r, $g, $b);
    
    $char = mb_chr($codepoint);
    echo "Codepoint: 0x" . dechex($codepoint) . " Char length: " . strlen($char) . "\n";
    
    $fontSize = 90;
    $bbox = imagettfbbox($fontSize, 0, $font, $char);
    $x = ($size - ($bbox[2] - $bbox[0])) / 2 - $bbox[0];
    $y = ($size - ($bbox[1] - $bbox[7])) / 2 - $bbox[7];
    imagettftext($img, $fontSize, 0, (int)$x, (int)$y, $col, $font, $char);
    
    imagepng($img, $outDir . $filename);
    imagedestroy($img);
    echo "Generated: $filename\n";
}

createIcon('icon-student.png', 0xF501, '#1e3a8a'); // fa-user-graduate
createIcon('icon-cert.png', 0xF0A3, '#1e3a8a');   // fa-certificate
createIcon('icon-date.png', 0xF073, '#1e3a8a');   // fa-calendar-alt
createIcon('icon-course.png', 0xF091, '#f26522'); // fa-trophy
echo "Done!\n";
