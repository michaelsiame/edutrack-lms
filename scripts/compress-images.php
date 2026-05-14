<?php
/**
 * Image Compression Script
 * Compresses images in public/assets/images/ for smaller file sizes
 */

$baseDir = __DIR__ . '/../public/assets/images';
$backupDir = __DIR__ . '/../storage/backups/images-' . date('Ymd-His');

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$stats = ['compressed' => 0, 'skipped' => 0, 'errors' => 0, 'saved' => 0];

function humanSize($bytes) {
    if ($bytes > 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes > 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function compressImage($sourcePath, $backupDir) {
    global $stats;

    $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    $filename = basename($sourcePath);
    $originalSize = filesize($sourcePath);

    if ($ext === 'svg' || $originalSize < 5120) {
        $stats['skipped']++;
        return;
    }

    copy($sourcePath, $backupDir . '/' . $filename);

    $info = getimagesize($sourcePath);
    if (!$info) {
        $stats['skipped']++;
        return;
    }

    list($width, $height, $type) = $info;

    switch ($type) {
        case IMAGETYPE_JPEG:
            $srcImage = imagecreatefromjpeg($sourcePath);
            $quality = 70;
            break;
        case IMAGETYPE_PNG:
            $srcImage = imagecreatefrompng($sourcePath);
            imagealphablending($srcImage, true);
            imagesavealpha($srcImage, true);
            $quality = 9;
            break;
        default:
            $stats['skipped']++;
            return;
    }

    if (!$srcImage) {
        $stats['errors']++;
        return;
    }

    // Determine max dimension based on image type
    $maxDimension = 1920;
    $isLogo = stripos($filename, 'logo') !== false;
    if ($isLogo && $type == IMAGETYPE_PNG) {
        $maxDimension = 400; // Logos don't need to be huge
    }

    $newWidth = $width;
    $newHeight = $height;

    if ($width > $maxDimension || $height > $maxDimension) {
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = intval($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = intval($width * ($maxDimension / $height));
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($type == IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($srcImage);
        $srcImage = $resized;
    }

    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($srcImage, $sourcePath, $quality);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($srcImage, $sourcePath, $quality);
            break;
    }

    imagedestroy($srcImage);

    if ($success) {
        $newSize = filesize($sourcePath);
        $saved = $originalSize - $newSize;
        $percent = $originalSize > 0 ? round(($saved / $originalSize) * 100, 1) : 0;
        $stats['compressed']++;
        $stats['saved'] += $saved;
        echo "  OK: {$filename} {$width}x{$height} -> {$newWidth}x{$newHeight} | " . humanSize($originalSize) . " -> " . humanSize($newSize) . " (saved {$percent}%)\n";
    } else {
        echo "  ERROR: {$filename}\n";
        $stats['errors']++;
    }
}

echo "Image Compression Tool\n";
echo "======================\n";
echo "Source: {$baseDir}\n";
echo "Backup: {$backupDir}\n\n";

$files = glob($baseDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);

foreach ($files as $file) {
    compressImage($file, $backupDir);
}

echo "\n======================\n";
echo "Done!\n";
echo "Compressed: {$stats['compressed']}\n";
echo "Skipped:    {$stats['skipped']}\n";
echo "Errors:     {$stats['errors']}\n";
echo "Total saved: " . humanSize($stats['saved']) . "\n";
