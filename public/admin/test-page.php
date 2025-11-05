<?php
/**
 * Test Page - Shows exact errors
 */

// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!-- Starting test page -->\n";

try {
    echo "Step 1: Loading middleware...<br>";
    require_once '../../src/middleware/admin-only.php';
    echo "✓ Middleware loaded<br>";

    echo "Step 2: Accessing database...<br>";
    $testQuery = $db->fetchAll("SELECT * FROM users LIMIT 1");
    echo "✓ Database query successful<br>";

    echo "Step 3: Loading header template...<br>";
    $page_title = 'Test Page';
    require_once '../../src/templates/admin-header.php';
    echo "✓ Header loaded<br>";

    ?>

    <div class="container-fluid px-4 py-6">
        <h1 class="text-3xl font-bold text-gray-900">Test Page Working!</h1>
        <p class="text-gray-600 mt-2">If you see this, the basic structure is working.</p>

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-bold mb-4">Test Query Results:</h2>
            <pre><?php print_r($testQuery); ?></pre>
        </div>
    </div>

    <?php
    require_once '../../src/templates/admin-footer.php';

} catch (Throwable $e) {
    echo "<div style='background:red;color:white;padding:20px;margin:20px;'>";
    echo "<h1>ERROR CAUGHT:</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
