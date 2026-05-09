<?php
/**
 * Certificate Preview (Debug)
 * Renders the certificate HTML template directly in the browser.
 * Useful for debugging layout issues without involving TCPDF.
 *
 * Access: logged-in owner or admin only.
 * Usage: certificate-preview.php?id=18
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/Certificate.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

$certificateId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$certificateId) {
    http_response_code(400);
    echo '<h1>Bad Request</h1><p>Missing or invalid certificate ID.</p>';
    exit;
}

error_log("[CERT-DEBUG] certificate-preview.php: Preview request for cert_id={$certificateId}, user_id={$userId}");

try {
    $certificate = Certificate::find($certificateId);

    if (!$certificate) {
        error_log("[CERT-DEBUG] certificate-preview.php: Certificate not found id={$certificateId}");
        http_response_code(404);
        echo '<h1>Not Found</h1><p>Certificate not found.</p>';
        exit;
    }

    // Verify ownership (allow admins)
    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    if ($certificate->getUserId() != $userId && !$isAdmin) {
        error_log("[CERT-DEBUG] certificate-preview.php: Permission denied for user_id={$userId}");
        http_response_code(403);
        echo '<h1>Forbidden</h1><p>You do not have permission to preview this certificate.</p>';
        exit;
    }

    error_log("[CERT-DEBUG] certificate-preview.php: Generating preview HTML...");
    $html = $certificate->getDebugHtml();

    if ($html === false) {
        error_log("[CERT-DEBUG] certificate-preview.php: getDebugHtml() returned false");
        http_response_code(500);
        echo '<h1>Preview Error</h1><p>Could not generate certificate preview. Check error logs.</p>';
        exit;
    }

    error_log("[CERT-DEBUG] certificate-preview.php: Preview HTML generated. Length=" . strlen($html) . " bytes");

    // Output the preview
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Preview — ID <?= (int)$certificateId ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #1f2937;
            margin: 0;
            padding: 20px;
            color: #e5e7eb;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        h1 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
        }
        .badge-ok { background: #065f46; color: #a7f3d0; }
        .badge-warn { background: #92400e; color: #fde68a; }
        .preview-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            margin-bottom: 24px;
            overflow-x: auto;
        }
        .debug-panel {
            background: #111827;
            border: 1px solid #374151;
            border-radius: 8px;
            padding: 16px;
            font-size: 0.8rem;
            line-height: 1.6;
        }
        .debug-panel h2 {
            font-size: 1rem;
            margin: 0 0 12px 0;
            color: #60a5fa;
        }
        .debug-panel pre {
            margin: 0;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            color: #9ca3af;
        }
        .debug-panel code {
            color: #34d399;
        }
        .actions {
            margin-bottom: 16px;
        }
        .actions a {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin-right: 8px;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #374151;
            color: #e5e7eb;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            Certificate Preview
            <span class="badge badge-ok">ID <?= (int)$certificateId ?></span>
            <span class="badge badge-warn">Debug Mode</span>
        </h1>

        <div class="actions">
            <a href="<?= url('download-certificate.php?id=' . $certificateId . '&action=download') ?>" class="btn-primary">⬇ Download PDF</a>
            <a href="<?= url('download-certificate.php?id=' . $certificateId) ?>" class="btn-secondary">View Details</a>
            <a href="<?= url('my-certificates.php') ?>" class="btn-secondary">My Certificates</a>
        </div>

        <div class="preview-box">
            <?= $html ?>
        </div>

        <div class="debug-panel">
            <h2>Certificate Data Dump</h2>
            <pre><code><?php print_r($certificate->getData()); ?></code></pre>
        </div>

        <div class="debug-panel" style="margin-top:16px;">
            <h2>Replacement Values</h2>
            <pre><code><?php
                $logoPath = PUBLIC_PATH . '/assets/images/logo.png';
                $tevetaLogoPath = PUBLIC_PATH . '/assets/images/teveta-logo.png';
                echo "logo.png exists: " . (file_exists($logoPath) ? 'YES' : 'NO') . " ({$logoPath})\n";
                echo "teveta-logo.png exists: " . (file_exists($tevetaLogoPath) ? 'YES' : 'NO') . " ({$tevetaLogoPath})\n";
                echo "student_name: " . strtoupper($certificate->getStudentName()) . "\n";
                echo "course_title: " . htmlspecialchars($certificate->getCourseTitle()) . "\n";
                echo "completion_date: " . date('F j, Y', strtotime($certificate->getIssuedAt() ?? 'now')) . "\n";
                echo "certificate_number: " . $certificate->getCertificateNumber() . "\n";
                echo "verify_url: " . url('verify-certificate.php?code=' . $certificate->getVerificationCode()) . "\n";
                echo "instructor_name: " . ($certificate->getInstructorName() ?: 'Course Instructor') . "\n";
            ?></code></pre>
        </div>
    </div>
</body>
</html>
    <?php
    exit;

} catch (Exception $e) {
    error_log("[CERT-DEBUG] certificate-preview.php: EXCEPTION: " . get_class($e) . " — " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo '<h1>Server Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
