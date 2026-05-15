<?php
/**
 * Test Dompdf rendering of certificate template with dummy data
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$templatePath = __DIR__ . '/../../src/templates/certificate-dompdf.php';
$template = file_get_contents($templatePath);

$dummyData = [
    '{{logo_path}}'            => __DIR__ . '/../../public/assets/images/logo-sm.png',
    '{{teveta_logo_path}}'     => __DIR__ . '/../../public/assets/images/teveta-logo-sm.png',
    '{{corner_tl}}'            => __DIR__ . '/../../public/assets/images/cert-corner-tl.png',
    '{{corner_tr}}'            => __DIR__ . '/../../public/assets/images/cert-corner-tr.png',
    '{{corner_bl}}'            => __DIR__ . '/../../public/assets/images/cert-corner-bl.png',
    '{{corner_br}}'            => __DIR__ . '/../../public/assets/images/cert-corner-br.png',
    '{{seal_path}}'            => __DIR__ . '/../../public/assets/images/certificate-seal.png',
    '{{student_name}}'         => 'CATHERINE NAMAKANDA',
    '{{course_title}}'         => 'General Basic Computing',
    '{{merit_text}}'           => 'With Merit',
    '{{formal_date_html}}'     => '<strong>27<sup>th</sup></strong> day of <em>March</em> in the year <strong>2026</strong>',
    '{{completion_date}}'      => 'March 27, 2026',
    '{{certificate_number}}'   => 'NRC 2495807/1/1',
    '{{student_number}}'       => '26Edu249580',
    '{{verify_url}}'           => 'https://edutrackzambia.com/verify-certificate.php?code=TEST123',
];

$html = str_replace(array_keys($dummyData), array_values($dummyData), $template);

// Remove images that don't exist to avoid Dompdf errors
$html = preg_replace_callback('/<img[^>]+src="[^"]*"[^>]*>/i', function($matches) {
    preg_match('/src="([^"]*)"/', $matches[0], $srcMatch);
    $src = $srcMatch[1] ?? '';
    if ($src && file_exists($src)) {
        return $matches[0];
    }
    return '<!-- missing image: ' . htmlspecialchars($src) . ' -->';
}, $html);

echo "Template loaded. Length: " . strlen($html) . " bytes\n";

try {
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isPhpEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('chroot', __DIR__ . '/../../public');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    
    echo "Rendering...\n";
    $dompdf->render();
    
    $output = $dompdf->output();
    $pdfPath = __DIR__ . '/../../storage/cache/cert-dompdf-test.pdf';
    file_put_contents($pdfPath, $output);
    
    echo "SUCCESS! PDF generated: {$pdfPath}\n";
    echo "Size: " . strlen($output) . " bytes\n";
    
} catch (Exception $e) {
    echo "ERROR: " . get_class($e) . " — " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} catch (Error $e) {
    echo "FATAL: " . get_class($e) . " — " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
