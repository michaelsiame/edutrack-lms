<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MigrateData extends Command
{
    protected $signature = 'migrate:data 
                            {--source-db=edutrack_legacy : Source database name}
                            {--batch=1000 : Batch size for large tables}
                            {--table= : Specific table to migrate}';

    protected $description = 'Migrate data from legacy database to Laravel schema';

    protected array $tables = [
        'users',
        'user_roles',
        'user_profiles',
        'instructors',
        'course_categories',
        'courses',
        'modules',
        'lessons',
        'enrollments',
        'certificates',
        'payments',
        'quizzes',
        'assignments',
        'questions',
        'quiz_attempts',
        'announcements',
        'activity_logs',
    ];

    public function handle(): int
    {
        $sourceDb = $this->option('source-db');
        $batchSize = (int) $this->option('batch');
        $specificTable = $this->option('table');

        // Verify source database exists
        $databases = DB::select('SHOW DATABASES');
        $dbNames = array_map(fn($db) => $db->Database, $databases);
        
        if (!in_array($sourceDb, $dbNames)) {
            $this->error("Source database '{$sourceDb}' not found.");
            return self::FAILURE;
        }

        $tablesToMigrate = $specificTable ? [$specificTable] : $this->tables;

        foreach ($tablesToMigrate as $table) {
            $this->migrateTable($sourceDb, $table, $batchSize);
        }

        $this->info('Data migration completed!');
        return self::SUCCESS;
    }

    protected function migrateTable(string $sourceDb, string $table, int $batchSize): void
    {
        $this->info("Migrating table: {$table}");

        $count = DB::connection()->selectOne("SELECT COUNT(*) as total FROM {$sourceDb}.{$table}")->total;
        
        if ($count === 0) {
            $this->warn("  Table {$table} is empty. Skipping.");
            return;
        }

        $this->info("  Found {$count} records");

        $migrated = 0;
        $offset = 0;

        while ($offset < $count) {
            $rows = DB::select("SELECT * FROM {$sourceDb}.{$table} LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($rows as $row) {
                $data = (array) $row;
                
                // Transform data based on table-specific rules
                $data = $this->transformData($table, $data);

                try {
                    DB::table($table)->updateOrInsert(
                        $this->getUniqueKey($table, $data),
                        $data
                    );
                    $migrated++;
                } catch (\Exception $e) {
                    $this->error("  Failed to migrate record in {$table}: " . $e->getMessage());
                }
            }

            $offset += $batchSize;
            $this->info("  Progress: {$migrated}/{$count}");
        }

        $this->info("  Migrated {$migrated} records to {$table}");
    }

    protected function transformData(string $table, array $data): array
    {
        // Remove columns that don't exist in new schema
        $data = $this->filterColumns($table, $data);

        // Table-specific transformations
        switch ($table) {
            case 'users':
                // Ensure password_hash exists
                if (empty($data['password_hash']) && !empty($data['password'])) {
                    $data['password_hash'] = $data['password'];
                    unset($data['password']);
                }
                break;

            case 'certificates':
                // Map legacy certificate fields
                if (isset($data['certificate_id'])) {
                    $data['id'] = $data['certificate_id'];
                    unset($data['certificate_id']);
                }
                break;

            case 'payments':
                if (isset($data['payment_id'])) {
                    $data['id'] = $data['payment_id'];
                    unset($data['payment_id']);
                }
                break;
        }

        return $data;
    }

    protected function filterColumns(string $table, array $data): array
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        return array_intersect_key($data, array_flip($columns));
    }

    protected function getUniqueKey(string $table, array $data): array
    {
        return match ($table) {
            'users' => ['id' => $data['id'] ?? null],
            'certificates' => ['certificate_id' => $data['certificate_id'] ?? $data['id'] ?? null],
            'payments' => ['payment_id' => $data['payment_id'] ?? $data['id'] ?? null],
            default => ['id' => $data['id'] ?? null],
        };
    }
}
