<?php
/**
 * Generate certificate corner decoration images
 */

$size = 40;
$orange = [242, 101, 34]; // #F26522

function createCorner($filename, $flipH = false, $flipV = false) {
    global $size, $orange;
    $img = imagecreatetruecolor($size, $size);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagesavealpha($img, true);
    
    $color = imagecolorallocate($img, $orange[0], $orange[1], $orange[2]);
    
    // Draw right triangle
    $points = [0, 0, $size, 0, 0, $size];
    if ($flipH && $flipV) {
        $points = [$size, $size, 0, $size, $size, 0];
    } elseif ($flipH) {
        $points = [$size, 0, 0, 0, $size, $size];
    } elseif ($flipV) {
        $points = [0, $size, $size, $size, 0, 0];
    }
    
    imagefilledpolygon($img, $points, 3, $color);
    
    // Add a thin blue line on the hypotenuse for style
    $blue = imagecolorallocate($img, 30, 74, 138);
    imagesetthickness($img, 2);
    if ($flipH && $flipV) {
        imageline($img, 0, $size, $size, 0, $blue);
    } elseif ($flipH) {
        imageline($img, 0, 0, $size, $size, $blue);
    } elseif ($flipV) {
        imageline($img, $size, 0, 0, $size, $blue);
    } else {
        imageline($img, $size, 0, 0, $size, $blue);
    }
    
    imagepng($img, $filename);
    imagedestroy($img);
}

$outDir = __DIR__ . '/../../public/assets/images/';
createCorner($outDir . 'cert-corner-tl.png', false, false);
createCorner($outDir . 'cert-corner-tr.png', true, false);
createCorner($outDir . 'cert-corner-bl.png', false, true);
createCorner($outDir . 'cert-corner-br.png', true, true);

echo "Corner images generated.\n";
