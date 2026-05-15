<?php
/**
 * Validate certificate templates placeholder coverage
 */

$templates = [
    __DIR__ . '/../../src/templates/certificate-dompdf.php',
    __DIR__ . '/../../src/templates/certificate-pdf.php',
];

// Extract placeholders from Certificate.php buildCertificateHtml()
$certFile = file_get_contents(__DIR__ . '/../../src/classes/Certificate.php');

// Find all replacement keys
preg_match_all("/'\{\{([a-z_]+)\}\}'\s*=>/", $certFile, $certMatches);
$certPlaceholders = array_unique($certMatches[1]);

echo "Placeholders defined in Certificate.php (" . count($certPlaceholders) . "):\n";
sort($certPlaceholders);
foreach ($certPlaceholders as $p) {
    echo "  {{$p}}\n";
}

echo "\n";

foreach ($templates as $templatePath) {
    $name = basename($templatePath);
    $content = file_get_contents($templatePath);
    
    preg_match_all('/\{\{([a-z_]+)\}\}/', $content, $matches);
    $templatePlaceholders = array_unique($matches[1]);
    
    echo "=== {$name} ===\n";
    echo "Placeholders used (" . count($templatePlaceholders) . "):\n";
    sort($templatePlaceholders);
    foreach ($templatePlaceholders as $p) {
        echo "  {{$p}}\n";
    }
    
    $missing = array_diff($templatePlaceholders, $certPlaceholders);
    $extra = array_diff($certPlaceholders, $templatePlaceholders);
    
    if (!empty($missing)) {
        echo "\nMISSING in Certificate.php:\n";
        foreach ($missing as $p) {
            echo "  {{$p}}\n";
        }
    }
    
    if (!empty($extra)) {
        echo "\nExtra in Certificate.php (not used in this template):\n";
        foreach ($extra as $p) {
            echo "  {{$p}}\n";
        }
    }
    
    if (empty($missing) && empty($extra)) {
        echo "\nAll placeholders match perfectly.\n";
    }
    
    echo "\n";
}
