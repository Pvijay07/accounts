<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables imported without PRIMARY KEY / AUTO_INCREMENT on `id`.
     *
     * @var array<string, string>
     */
    private array $tables = [
        'activity_logs' => 'BIGINT UNSIGNED',
        'attachments' => 'BIGINT UNSIGNED',
        'audit_logs' => 'BIGINT UNSIGNED',
        'balances' => 'BIGINT UNSIGNED',
        'categories' => 'INT',
        'category_assignments' => 'INT UNSIGNED',
        'ca_statements' => 'BIGINT UNSIGNED',
        'ca_task_templates' => 'BIGINT UNSIGNED',
        'companies' => 'BIGINT UNSIGNED',
        'compliance_tasks' => 'BIGINT UNSIGNED',
        'expenses' => 'BIGINT UNSIGNED',
        'expense_generation_logs' => 'BIGINT UNSIGNED',
        'expense_types' => 'BIGINT UNSIGNED',
        'failed_jobs' => 'BIGINT UNSIGNED',
        'gst_settlements' => 'BIGINT UNSIGNED',
        'gst_tasks' => 'BIGINT UNSIGNED',
        'incomes' => 'BIGINT UNSIGNED',
        'invoices' => 'BIGINT UNSIGNED',
        'jobs' => 'BIGINT UNSIGNED',
        'migrations' => 'INT UNSIGNED',
        'notifications' => 'BIGINT UNSIGNED',
        'permissions' => 'INT',
        'personal_access_tokens' => 'BIGINT UNSIGNED',
        'receipts' => 'INT',
        'role_permissions' => 'INT',
        'system_settings' => 'BIGINT UNSIGNED',
        'taxes' => 'BIGINT UNSIGNED',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $database = config('database.connections.mysql.database');

        foreach ($this->tables as $table => $columnType) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            $column = DB::selectOne(
                'SELECT EXTRA, COLUMN_KEY
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$database, $table, 'id']
            );

            if (!$column) {
                continue;
            }

            $alreadyIncrementing = str_contains(strtolower((string) $column->EXTRA), 'auto_increment');
            $alreadyPrimary = strtoupper((string) $column->COLUMN_KEY) === 'PRI';

            if ($alreadyIncrementing && $alreadyPrimary) {
                continue;
            }

            $stats = DB::table($table)
                ->selectRaw('COUNT(*) AS total_rows, COUNT(id) AS id_rows, COUNT(DISTINCT id) AS distinct_ids')
                ->first();

            if (
                !$stats
                || (int) $stats->total_rows !== (int) $stats->id_rows
                || (int) $stats->total_rows !== (int) $stats->distinct_ids
            ) {
                throw new RuntimeException("Cannot repair {$table}.id safely because the existing values are null or duplicated.");
            }

            $sql = sprintf(
                'ALTER TABLE `%s` MODIFY `id` %s NOT NULL AUTO_INCREMENT%s',
                $table,
                $columnType,
                $alreadyPrimary ? '' : ', ADD PRIMARY KEY (`id`)'
            );

            DB::statement($sql);
        }
    }

    public function down(): void
    {
        // Intentionally irreversible for imported live tables.
    }
};
