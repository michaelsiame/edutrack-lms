<?php
$outDir = __DIR__ . '/../public/assets/images/cert-corners/';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

function makeCorner($filename, $size, $outerColor, $innerColor, $flipH = false, $flipV = false) {
    global $outDir;
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    
    // Parse colors
    $o = [
        hexdec(substr($outerColor, 1, 2)),
        hexdec(substr($outerColor, 3, 2)),
        hexdec(substr($outerColor, 5, 2))
    ];
    $i = [
        hexdec(substr($innerColor, 1, 2)),
        hexdec(substr($innerColor, 3, 2)),
        hexdec(substr($innerColor, 5, 2))
    ];
    $oCol = imagecolorallocate($img, $o[0], $o[1], $o[2]);
    $iCol = imagecolorallocate($img, $i[0], $i[1], $i[2]);
    
    // Draw outer triangle
    $points = [0, 0, $size, 0, 0, $size];
    if ($flipH) { for ($p = 0; $p < 6; $p += 2) $points[$p] = $size - $points[$p]; }
    if ($flipV) { for ($p = 1; $p < 6; $p += 2) $points[$p] = $size - $points[$p]; }
    imagefilledpolygon($img, $points, 3, $oCol);
    
    // Draw inner triangle (slightly smaller)
    $pad = 4;
    $points2 = [$pad, $pad, $size - $pad*2, $pad, $pad, $size - $pad*2];
    if ($flipH) { for ($p = 0; $p < 6; $p += 2) $points2[$p] = $size - $points2[$p]; }
    if ($flipV) { for ($p = 1; $p < 6; $p += 2) $points2[$p] = $size - $points2[$p]; }
    imagefilledpolygon($img, $points2, 3, $iCol);
    
    // Draw white line
    $wCol = imagecolorallocate($img, 255, 255, 255);
    $l1 = [$pad, $size - $pad*3, $size - $pad*3, $pad];
    $l2 = [$pad + 1, $size - $pad*3, $size - $pad*3, $pad + 1];
    if ($flipH) { for ($p = 0; $p < 4; $p += 2) { $l1[$p] = $size - $l1[$p]; $l2[$p] = $size - $l2[$p]; } }
    if ($flipV) { for ($p = 1; $p < 4; $p += 2) { $l1[$p] = $size - $l1[$p]; $l2[$p] = $size - $l2[$p]; } }
    imageline($img, $l1[0], $l1[1], $l1[2], $l1[3], $wCol);
    
    imagepng($img, $outDir . $filename);
    imagedestroy($img);
    echo "Generated: $filename\n";
}

$size = 60;
makeCorner('tl.png', $size, '#f26522', '#1e3a8a', false, false);
makeCorner('tr.png', $size, '#f26522', '#1e3a8a', true, false);
makeCorner('bl.png', $size, '#f26522', '#1e3a8a', false, true);
makeCorner('br.png', $size, '#f26522', '#1e3a8a', true, true);
echo "Done!\n";
