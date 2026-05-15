<?php
/**
 * Test certificate template generation from CLI
 * Usage: php scripts/tools/test-certificate-template.php [certificate_id]
 */

define('PUBLIC_PATH', __DIR__ . '/../../public');
define('SRC_PATH', __DIR__ . '/../../src');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/includes/config.php';
require_once __DIR__ . '/../../src/includes/database.php';
require_once __DIR__ . '/../../src/classes/Certificate.php';

$certificateId = $argv[1] ?? 1;

try {
    $certificate = Certificate::find($certificateId);
    
    if (!$certificate) {
        echo "Certificate not found: {$certificateId}\n";
        exit(1);
    }
    
    echo "Certificate ID: {$certificate->getId()}\n";
    echo "Student: {$certificate->getStudentName()}\n";
    echo "Course: {$certificate->getCourseTitle()}\n";
    echo "Score: {$certificate->getFinalScore()}\n";
    echo "Merit: {$certificate->getMeritText()}\n";
    echo "Certificate Number: {$certificate->getCertificateNumber()}\n";
    echo "\n";
    
    $html = $certificate->getDebugHtml();
    
    if ($html === false) {
        echo "ERROR: getDebugHtml() returned false\n";
        exit(1);
    }
    
    $outputFile = __DIR__ . '/../../storage/cache/cert-test-' . $certificateId . '.html';
    file_put_contents($outputFile, $html);
    
    echo "HTML generated successfully. Length: " . strlen($html) . " bytes\n";
    echo "Saved to: {$outputFile}\n";
    
    // Check for empty placeholders
    preg_match_all('/\{\{([a-z_]+)\}\}/', $html, $matches);
    if (!empty($matches[0])) {
        echo "\nRemaining placeholders:\n";
        foreach ($matches[0] as $placeholder) {
            echo "  - {$placeholder}\n";
        }
    } else {
        echo "\nAll placeholders replaced.\n";
    }
    
    // Try Dompdf generation
    if (class_exists('Dompdf\Dompdf')) {
        echo "\nTesting Dompdf generation...\n";
        $pdfContent = $certificate->generatePDF();
        if ($pdfContent !== false) {
            $pdfFile = __DIR__ . '/../../storage/cache/cert-test-' . $certificateId . '.pdf';
            file_put_contents($pdfFile, $pdfContent);
            echo "Dompdf PDF generated: {$pdfFile} (" . strlen($pdfContent) . " bytes)\n";
        } else {
            echo "Dompdf PDF generation FAILED\n";
        }
    } else {
        echo "\nDompdf not available, skipping PDF test.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . get_class($e) . " — " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
