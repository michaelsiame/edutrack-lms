<?php
/**
 * Renders resources/views/certificates/template.blade.php to
 * public/assets/images/certificate-template.png via wkhtmltopdf.
 *
 * Run once when the static design changes; the resulting PNG is committed
 * and used at runtime by App\Services\CertificateService as the background
 * for every generated certificate. This keeps the certificate's fixed
 * design pixel-perfect (Google Fonts, gradients, full CSS) without needing
 * wkhtmltopdf available on Hostinger.
 *
 * Requires wkhtmltopdf locally (apt install wkhtmltopdf). Output:
 *   public/assets/images/certificate-template.png
 *
 * Usage: php scripts/generate-certificate-template.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$app->instance('request', Illuminate\Http\Request::create('http://localhost/', 'GET'));

$tmpHtml = sys_get_temp_dir() . '/cert_template_' . getmypid() . '.html';
$tmpPdf  = sys_get_temp_dir() . '/cert_template_' . getmypid() . '.pdf';
$tmpPng  = sys_get_temp_dir() . '/cert_template_' . getmypid();
$outPng  = __DIR__ . '/../public/assets/images/certificate-template.png';

$html = view('certificates.template')->render();
file_put_contents($tmpHtml, $html);

// wkhtmltopdf 0.12.6 under-renders A4 height when given --page-size A4; the
// --dpi 96 + --zoom 1.37 combo gets it back to the full 297mm.
$cmd = sprintf(
    'wkhtmltopdf --page-size A4 --orientation Portrait '
    . '--margin-top 0mm --margin-bottom 0mm --margin-left 0mm --margin-right 0mm '
    . '--enable-local-file-access --javascript-delay 3000 --dpi 96 --zoom 1.37 '
    . '%s %s 2>&1',
    escapeshellarg($tmpHtml),
    escapeshellarg($tmpPdf)
);
echo "Rendering HTML -> PDF...\n";
exec($cmd, $out, $ret);
if ($ret !== 0 || !file_exists($tmpPdf)) {
    fwrite(STDERR, "wkhtmltopdf failed:\n" . implode("\n", $out) . "\n");
    exit(1);
}

echo "Converting PDF -> PNG (200 DPI)...\n";
exec(sprintf('pdftoppm -r 200 %s %s -png -f 1 -l 1 2>&1', escapeshellarg($tmpPdf), escapeshellarg($tmpPng)), $out, $ret);
$rawPng = $tmpPng . '-1.png';
if (!file_exists($rawPng)) {
    fwrite(STDERR, "pdftoppm failed: " . implode("\n", $out) . "\n");
    exit(1);
}

// wkhtmltopdf still leaves ~75mm of empty space below the cert content even
// with the zoom workaround. Crop that and stretch the cert portion to fill A4.
// Empirically the cert content ends ~1770 px down in a 2339-px-tall image.
echo "Cropping cert content and stretching to A4...\n";
$contentH = 1770;
$targetH = 2339;
exec(sprintf(
    'convert %s -crop "1653x%dx+0+0" -resize "1653x%d!" %s 2>&1',
    escapeshellarg($rawPng),
    $contentH,
    $targetH,
    escapeshellarg($outPng)
), $out, $ret);

if (!file_exists($outPng)) {
    fwrite(STDERR, "ImageMagick failed: " . implode("\n", $out) . "\n");
    exit(1);
}

unlink($tmpHtml);
unlink($tmpPdf);
@unlink($rawPng);

echo "Written: $outPng (" . round(filesize($outPng) / 1024) . " KB)\n";
