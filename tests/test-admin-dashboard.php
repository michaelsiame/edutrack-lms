<?php
/**
 * Admin Dashboard Comprehensive Test Script
 * Tests all admin pages for errors and database issues
 *
 * Usage: php tests/test-admin-dashboard.php
 * Or via browser: http://localhost/edutrack-lms/tests/test-admin-dashboard.php
 */

// Configuration
define('BASE_URL', 'http://localhost/edutrack-lms/public');
define('ADMIN_BASE', BASE_URL . '/admin');

// Test results
$results = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Test pages
$testPages = [
    'Main Dashboard' => [
        'url' => '/admin/index.php',
        'description' => 'Admin dashboard main page'
    ],
    'Users Management' => [
        'url' => '/admin/users/index.php',
        'description' => 'List all users'
    ],
    'Create User' => [
        'url' => '/admin/users/create.php',
        'description' => 'Create new user form'
    ],
    'Students Management' => [
        'url' => '/admin/students/index.php',
        'description' => 'List all students'
    ],
    'Courses Management' => [
        'url' => '/admin/courses/index.php',
        'description' => 'List all courses'
    ],
    'Create Course' => [
        'url' => '/admin/courses/create.php',
        'description' => 'Create new course form'
    ],
    'Enrollments' => [
        'url' => '/admin/enrollments/index.php',
        'description' => 'List all enrollments'
    ],
    'Certificates' => [
        'url' => '/admin/certificates/index.php',
        'description' => 'List all certificates'
    ],
    'Issue Certificate' => [
        'url' => '/admin/certificates/issue.php',
        'description' => 'Issue certificate form'
    ],
    'Reports Dashboard' => [
        'url' => '/admin/reports/index.php',
        'description' => 'Reports overview'
    ],
    'Settings' => [
        'url' => '/admin/settings/index.php',
        'description' => 'General settings'
    ],
];

/**
 * Test a single page
 */
function testPage($name, $config) {
    global $totalTests, $passedTests, $failedTests;

    $totalTests++;
    $url = BASE_URL . $config['url'];

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Testing: $name\n";
    echo "URL: $url\n";
    echo "Description: {$config['description']}\n";
    echo str_repeat('-', 80) . "\n";

    $result = [
        'name' => $name,
        'url' => $url,
        'description' => $config['description'],
        'status' => 'UNKNOWN',
        'errors' => [],
        'warnings' => [],
        'response_code' => null,
        'response_time' => null,
    ];

    // Start timer
    $startTime = microtime(true);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // End timer
    $endTime = microtime(true);
    $result['response_time'] = round(($endTime - $startTime) * 1000, 2); // ms
    $result['response_code'] = $httpCode;

    // Check for cURL errors
    if ($curlError) {
        $result['status'] = 'FAILED';
        $result['errors'][] = "cURL Error: $curlError";
        echo "❌ FAILED - cURL Error: $curlError\n";
        $failedTests++;
        return $result;
    }

    // Check HTTP status code
    if ($httpCode !== 200 && $httpCode !== 302) {
        $result['status'] = 'FAILED';
        $result['errors'][] = "HTTP Status: $httpCode";
        echo "❌ FAILED - HTTP Status: $httpCode\n";
        $failedTests++;
        return $result;
    }

    // Check for PHP errors
    if (preg_match('/Fatal error:/i', $response)) {
        $result['status'] = 'FAILED';
        $result['errors'][] = 'PHP Fatal Error detected';

        // Extract error message
        if (preg_match('/Fatal error:(.+?)<\/b>/i', $response, $matches)) {
            $result['errors'][] = trim($matches[1]);
        }
        echo "❌ FAILED - PHP Fatal Error\n";
        $failedTests++;
        return $result;
    }

    // Check for database errors
    if (preg_match('/SQLSTATE\[(\w+)\]: (.+?)(?:<br|in \/)/i', $response, $matches)) {
        $result['status'] = 'FAILED';
        $result['errors'][] = "Database Error: {$matches[1]} - {$matches[2]}";
        echo "❌ FAILED - Database Error: {$matches[1]}\n";
        $failedTests++;
        return $result;
    }

    // Check for column not found errors
    if (preg_match('/Column not found.*Unknown column/i', $response)) {
        $result['status'] = 'FAILED';
        $result['errors'][] = 'Database column mismatch error';
        echo "❌ FAILED - Column not found error\n";
        $failedTests++;
        return $result;
    }

    // Check for warnings
    if (preg_match_all('/Warning:(.+?)<\/b>/i', $response, $matches)) {
        foreach ($matches[1] as $warning) {
            $result['warnings'][] = trim($warning);
        }
    }

    // Check for DEBUG comments indicating errors
    if (preg_match('/<!-- DEBUG: FATAL ERROR: (.+?) -->/i', $response, $matches)) {
        $result['status'] = 'FAILED';
        $result['errors'][] = "Debug Error: {$matches[1]}";
        echo "❌ FAILED - Debug Error: {$matches[1]}\n";
        $failedTests++;
        return $result;
    }

    // Check if response is too short (might indicate error)
    if (strlen($response) < 100 && $httpCode === 200) {
        $result['status'] = 'WARNING';
        $result['warnings'][] = 'Response too short - possible blank page';
        echo "⚠️  WARNING - Response too short ({strlen($response)} bytes)\n";
    }

    // If we got here, the test passed
    if ($result['status'] === 'UNKNOWN') {
        $result['status'] = 'PASSED';
        echo "✅ PASSED - Response: {$result['response_time']}ms\n";
        $passedTests++;
    }

    // Show warnings if any
    if (!empty($result['warnings'])) {
        echo "\nWarnings:\n";
        foreach ($result['warnings'] as $warning) {
            echo "  ⚠️  $warning\n";
        }
    }

    return $result;
}

/**
 * Print summary report
 */
function printSummary($results) {
    global $totalTests, $passedTests, $failedTests;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "TEST SUMMARY\n";
    echo str_repeat('=', 80) . "\n\n";

    echo "Total Tests: $totalTests\n";
    echo "✅ Passed: $passedTests\n";
    echo "❌ Failed: $failedTests\n";

    $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
    echo "\nSuccess Rate: {$successRate}%\n";

    // List failed tests
    if ($failedTests > 0) {
        echo "\n" . str_repeat('-', 80) . "\n";
        echo "FAILED TESTS:\n";
        echo str_repeat('-', 80) . "\n";

        foreach ($results as $result) {
            if ($result['status'] === 'FAILED') {
                echo "\n❌ {$result['name']}\n";
                echo "   URL: {$result['url']}\n";
                foreach ($result['errors'] as $error) {
                    echo "   Error: $error\n";
                }
            }
        }
    }

    // List warnings
    $warningCount = 0;
    foreach ($results as $result) {
        if (!empty($result['warnings'])) {
            $warningCount++;
        }
    }

    if ($warningCount > 0) {
        echo "\n" . str_repeat('-', 80) . "\n";
        echo "TESTS WITH WARNINGS:\n";
        echo str_repeat('-', 80) . "\n";

        foreach ($results as $result) {
            if (!empty($result['warnings'])) {
                echo "\n⚠️  {$result['name']}\n";
                echo "   URL: {$result['url']}\n";
                foreach ($result['warnings'] as $warning) {
                    echo "   Warning: $warning\n";
                }
            }
        }
    }

    echo "\n" . str_repeat('=', 80) . "\n";

    if ($failedTests === 0) {
        echo "🎉 ALL TESTS PASSED! The admin dashboard is working correctly.\n";
    } else {
        echo "⚠️  SOME TESTS FAILED. Please review the errors above.\n";
    }

    echo str_repeat('=', 80) . "\n";
}

// Set output format
if (php_sapi_name() === 'cli') {
    // CLI mode - plain text output
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "ADMIN DASHBOARD COMPREHENSIVE TEST\n";
    echo str_repeat('=', 80) . "\n";
} else {
    // Web mode - HTML output
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Admin Dashboard Test</title>';
    echo '<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}pre{background:#fff;padding:20px;border-radius:5px;}</style>';
    echo '</head><body><pre>';
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "ADMIN DASHBOARD COMPREHENSIVE TEST\n";
    echo str_repeat('=', 80) . "\n";
}

// Run tests
foreach ($testPages as $name => $config) {
    $results[] = testPage($name, $config);
}

// Print summary
printSummary($results);

// Close HTML if in web mode
if (php_sapi_name() !== 'cli') {
    echo '</pre></body></html>';
}

// Exit with appropriate code
exit($failedTests > 0 ? 1 : 0);
