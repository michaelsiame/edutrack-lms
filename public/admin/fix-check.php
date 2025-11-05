<?php
/**
 * Admin Pages Fix Checker
 * Diagnoses why admin pages appear blank
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Pages Fix Checker</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .test { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        .warning { border-color: #ffc107; background: #fff3cd; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
        .action { background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 10px 0; }
        pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Admin Pages Diagnostic Report</h1>

    <?php
    $issues = [];
    $warnings = [];

    // Test 1: Check if we can view source of a page
    echo '<div class="test">';
    echo '<h3>Test 1: Checking Admin Students Page</h3>';

    $testFile = __DIR__ . '/students/index.php';
    if (file_exists($testFile)) {
        $content = file_get_contents($testFile);
        $lines = count(file($testFile));
        echo "<p class='success'>✓ File exists with $lines lines</p>";

        // Check for BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            echo "<p class='error'>✗ FILE HAS UTF-8 BOM - This can cause blank pages!</p>";
            $issues[] = "UTF-8 BOM detected in students/index.php";
        } else {
            echo "<p class='success'>✓ No BOM detected</p>";
        }

        // Check for whitespace before <?php
        if (preg_match('/^\s+<\?php/', $content)) {
            echo "<p class='warning'>⚠ Whitespace before &lt;?php tag detected</p>";
            $warnings[] = "Whitespace before <?php in students/index.php";
        }

    } else {
        echo "<p class='error'>✗ File not found!</p>";
        $issues[] = "students/index.php not found";
    }
    echo '</div>';

    // Test 2: Try to include and catch errors
    echo '<div class="test">';
    echo '<h3>Test 2: Testing Page Execution</h3>';

    ob_start();
    try {
        $_GET = []; // Clear GET params
        include __DIR__ . '/students/index.php';
        $output = ob_get_clean();

        if (empty(trim($output))) {
            echo "<p class='error'>✗ Page executed but produced NO OUTPUT</p>";
            $issues[] = "Page executes but produces no output";
        } else {
            $outputLen = strlen($output);
            echo "<p class='success'>✓ Page executed and produced $outputLen bytes of output</p>";

            // Check if it's HTML
            if (stripos($output, '<!DOCTYPE') !== false || stripos($output, '<html') !== false) {
                echo "<p class='success'>✓ Output contains HTML</p>";

                // Save to file for inspection
                file_put_contents(__DIR__ . '/debug-output.html', $output);
                echo "<p>Output saved to: <code>public/admin/debug-output.html</code></p>";
                echo "<p><a href='debug-output.html' target='_blank' class='action'>View Captured Output</a></p>";
            } else {
                echo "<p class='warning'>⚠ Output doesn't look like HTML</p>";
                echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
            }
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo "<p class='error'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>File: " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "</p>";
        $issues[] = "Exception: " . $e->getMessage();
    }
    echo '</div>';

    // Test 3: Check browser cache
    echo '<div class="test warning">';
    echo '<h3>Test 3: Browser Cache Check</h3>';
    echo '<p>⚠ Your browser might be caching blank pages</p>';
    echo '<p><strong>Try these steps:</strong></p>';
    echo '<ol>';
    echo '<li>Press <code>Ctrl + Shift + Delete</code> (or <code>Cmd + Shift + Delete</code> on Mac)</li>';
    echo '<li>Select "Cached images and files"</li>';
    echo '<li>Click "Clear data"</li>';
    echo '<li>Or try opening pages in Incognito/Private mode</li>';
    echo '</ol>';
    echo '</div>';

    // Test 4: Check for JavaScript errors
    echo '<div class="test">';
    echo '<h3>Test 4: JavaScript Check</h3>';
    echo '<p>Open your browser\'s Developer Tools:</p>';
    echo '<ol>';
    echo '<li>Press <code>F12</code> or right-click and select "Inspect"</li>';
    echo '<li>Click the <strong>Console</strong> tab</li>';
    echo '<li>Visit an admin page</li>';
    echo '<li>Look for red error messages</li>';
    echo '</ol>';
    echo '<p>Common errors to look for:</p>';
    echo '<ul>';
    echo '<li>"Uncaught ReferenceError" - missing JavaScript library</li>';
    echo '<li>"Failed to load resource" - missing CSS/JS files</li>';
    echo '<li>"Alpine is not defined" - Alpine.js not loading</li>';
    echo '</ul>';
    echo '</div>';

    // Summary
    echo '<div class="test ' . (empty($issues) ? 'success' : 'error') . '">';
    echo '<h3>Summary</h3>';

    if (empty($issues)) {
        echo '<p class="success"><strong>✓ No critical issues found!</strong></p>';
        echo '<p>If pages are still blank, the most likely causes are:</p>';
        echo '<ol>';
        echo '<li><strong>Browser cache</strong> - Clear your browser cache (see Test 3)</li>';
        echo '<li><strong>JavaScript error</strong> - Check browser console (see Test 4)</li>';
        echo '<li><strong>CDN blocking</strong> - Check if Tailwind CSS and Alpine.js are loading</li>';
        echo '</ol>';
        echo '<p><a href="students/index.php" class="action">Try Opening Students Page</a></p>';
    } else {
        echo '<p class="error"><strong>✗ Issues Found:</strong></p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>' . htmlspecialchars($issue) . '</li>';
        }
        echo '</ul>';
    }

    if (!empty($warnings)) {
        echo '<p class="warning"><strong>⚠ Warnings:</strong></p>';
        echo '<ul>';
        foreach ($warnings as $warning) {
            echo '<li>' . htmlspecialchars($warning) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';

    echo '<div class="test">';
    echo '<h3>Quick Actions</h3>';
    echo '<p><a href="debug.php" class="action">Run System Debug</a></p>';
    echo '<p><a href="simple-test.php" class="action">Simple PHP Test</a></p>';
    echo '<p><a href="../" class="action">Back to Main Site</a></p>';
    echo '</div>';
    ?>

</div>
</body>
</html>
<?php
$finalOutput = ob_get_clean();
echo $finalOutput;
