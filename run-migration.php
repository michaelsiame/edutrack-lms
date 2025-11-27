<?php
/**
 * Run Live Sessions Migration
 * Execute this file once to create the live sessions tables
 */

require_once __DIR__ . '/src/bootstrap.php';

echo "=== Live Sessions Migration ===\n\n";

try {
    $db = Database::getInstance();

    // Read migration file
    $migrationFile = __DIR__ . '/database/migrations/add_live_sessions.sql';

    if (!file_exists($migrationFile)) {
        die("ERROR: Migration file not found at: $migrationFile\n");
    }

    $sql = file_get_contents($migrationFile);

    if (empty($sql)) {
        die("ERROR: Migration file is empty\n");
    }

    echo "Running migration from: $migrationFile\n\n";

    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        try {
            // Skip comments
            if (preg_match('/^\s*--/', $statement)) {
                continue;
            }

            $db->query($statement);
            $successCount++;

            // Extract table name from CREATE TABLE statement
            if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            } elseif (preg_match('/CREATE INDEX.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✓ Created index: {$matches[1]}\n";
            } else {
                echo "✓ Executed statement\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "✗ Error: " . $e->getMessage() . "\n";

            // If table already exists, that's okay
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "  (Table already exists - skipping)\n";
                $successCount++;
                $errorCount--;
            }
        }
    }

    echo "\n=== Migration Summary ===\n";
    echo "Successful: $successCount\n";
    echo "Errors: $errorCount\n";

    if ($errorCount === 0) {
        echo "\n✓ Migration completed successfully!\n";

        // Verify tables were created
        echo "\n=== Verifying Tables ===\n";

        $tables = ['live_sessions', 'live_session_attendance'];
        foreach ($tables as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
            if ($result) {
                echo "✓ Table '$table' exists\n";

                // Show column count
                $columns = $db->query("SHOW COLUMNS FROM $table")->fetchAll();
                echo "  - " . count($columns) . " columns\n";
            } else {
                echo "✗ Table '$table' NOT found\n";
            }
        }
    } else {
        echo "\n⚠ Migration completed with $errorCount error(s)\n";
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
