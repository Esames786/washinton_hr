<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan hr:fresh
 *
 * Safe "migrate:fresh" that ONLY touches hr_ prefixed tables.
 * Does NOT drop or affect any other tables (orders, users, etc.)
 * shared with washinton_agent on the same database.
 *
 * Steps:
 *  1. Drop all hr_ tables (in safe order to avoid FK constraint errors)
 *  2. Remove hr_ migration records from the migrations table
 *  3. Run all migrations (Laravel skips already-run ones, runs new hr_ ones)
 *  4. Run seeders in correct dependency order
 */
class HrFreshMigrate extends Command
{
    protected $signature   = 'hr:fresh {--seed : Run seeders after migration (default: yes)} {--no-seed : Skip seeders}';
    protected $description = 'Drop all hr_ tables, re-run HR migrations, and seed HR data (safe — does not touch shared tables)';

    /**
     * hr_ tables in DROP ORDER (children before parents to avoid FK errors).
     * Add new tables here as the project grows.
     */
    private array $hrTables = [
        // Tickets (leaf nodes)
        'hr_ticket_attachments',
        'hr_ticket_messages',
        'hr_employee_tickets',
        'hr_ticket_statuses',
        'hr_ticket_types',

        // Attendance & breaks
        'hr_employee_attendance_requests',
        'hr_employee_attendances',
        'hr_employee_breaks',

        // Leaves
        'hr_employee_assign_leaves',
        'hr_employee_leaves',
        'hr_leave_types',

        // Holidays
        'hr_employee_holiday_exceptions',
        'hr_holidays',

        // Working days
        'hr_employee_working_days',

        // Payroll
        'hr_payslip_adjustments',
        'hr_payslip_items',
        'hr_employee_payslips',
        'hr_payroll_details',
        'hr_payrolls',
        'hr_payroll_statuses',
        'hr_payroll_detail_statuses',
        'hr_payslip_item_types',

        // Tax
        'hr_employee_taxes',
        'hr_tax_slab_settings',

        // Gratuity
        'hr_gratuity_payouts',
        'hr_gratuity_payout_statuses',
        'hr_gratuity_balances',
        'hr_gratuity_settings',

        // Commission
        'hr_role_commission_settings',
        'hr_commission_settings',
        'hr_commission_target_types',
        'hr_commission_types',

        // Daily activity
        'hr_employee_daily_activities',
        'hr_role_activity_fields',
        'hr_daily_activity_fields',

        // Documents
        'hr_employee_documents',
        'hr_document_settings',

        // Petty cash
        'hr_petty_cash_transactions',
        'hr_petty_cash_master_histories',
        'hr_petty_cash_masters',
        'hr_petty_cash_heads',

        // Currency
        'hr_currency_rates',

        // Screenshots
        'hr_user_screenshots',

        // Employee status history
        'hr_employee_status_histories',

        // Bank details
        'hr_employee_bank_details',

        // Employees (depends on departments, designations, roles, shifts, etc.)
        'hr_employees',

        // Lookup tables
        'hr_employee_account_types',
        'hr_employee_statuses',
        'hr_employment_types',
        'hr_shift_attendance_rules',
        'hr_shift_types',
        'hr_attendance_statuses',
        'hr_designations',
        'hr_departments',
        'hr_role_gratuity_settings',

        // Roles / permissions (spatie)
        'hr_model_has_permissions',
        'hr_model_has_roles',
        'hr_role_has_permissions',
        'hr_permissions',
        'hr_roles',

        // Admins
        'hr_admins',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║           HR FRESH MIGRATE & SEED                ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        if (!$this->confirm('This will DROP all hr_ tables and re-seed. Continue?', true)) {
            $this->warn('Aborted.');
            return 1;
        }

        // ── STEP 1: Drop hr_ tables ──────────────────────────────────────
        $this->info('');
        $this->info('[ Step 1 ] Dropping hr_ tables...');

        Schema::disableForeignKeyConstraints();

        $dropped = 0;
        foreach ($this->hrTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->line("  ✓ Dropped: {$table}");
                $dropped++;
            } else {
                $this->line("  - Skipped (not found): {$table}");
            }
        }

        // Also catch any hr_ tables we may have missed
        $allTables = DB::select("SHOW TABLES");
        $dbName    = DB::getDatabaseName();
        $colKey    = "Tables_in_{$dbName}";

        foreach ($allTables as $row) {
            $tbl = $row->$colKey;
            if (str_starts_with($tbl, 'hr_') && Schema::hasTable($tbl)) {
                Schema::drop($tbl);
                $this->line("  ✓ Dropped (extra): {$tbl}");
                $dropped++;
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->info("  → {$dropped} table(s) dropped.");

        // ── STEP 2: Remove hr_ migration records ─────────────────────────
        $this->info('');
        $this->info('[ Step 2 ] Cleaning hr_ migration records...');

        // Get all HR migration file names
        $hrMigrationFiles = collect(glob(database_path('migrations/*.php')))
            ->map(fn($p) => pathinfo($p, PATHINFO_FILENAME))
            ->filter(fn($name) => $this->isMigrationHrRelated($name))
            ->values();

        $deleted = DB::table('migrations')
            ->whereIn('migration', $hrMigrationFiles)
            ->delete();

        $this->info("  → {$deleted} migration record(s) removed.");

        // ── STEP 3: Run migrations ────────────────────────────────────────
        $this->info('');
        $this->info('[ Step 3 ] Running migrations...');

        Artisan::call('migrate', ['--force' => true], $this->output);

        $this->info('  → Migrations complete.');

        // ── STEP 4: Run seeders ───────────────────────────────────────────
        if ($this->option('no-seed')) {
            $this->warn('  Seeders skipped (--no-seed flag).');
        } else {
            $this->info('');
            $this->info('[ Step 4 ] Running HR seeders...');

            $seeders = [
                \Database\Seeders\HrSettingSeeder::class,
                \Database\Seeders\HolidaySeeder::class,
                \Database\Seeders\ShiftSettingsSeeder::class,
                \Database\Seeders\SuperAdminSeeder::class,
                \Database\Seeders\PakistanReadySeeder::class,
                \Database\Seeders\TicketRequestTypesSeeder::class,
            ];

            foreach ($seeders as $seeder) {
                $name = class_basename($seeder);
                $this->line("  → Running {$name}...");
                Artisan::call('db:seed', [
                    '--class' => $seeder,
                    '--force' => true,
                ], $this->output);
            }

            $this->info('  → All seeders complete.');
        }

        // ── DONE ──────────────────────────────────────────────────────────
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║   ✓  HR FRESH COMPLETE — system is ready!        ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        return 0;
    }

    /**
     * Determine if a migration file is HR-related.
     * We identify HR migrations as anything that creates/modifies an hr_ table,
     * OR is one of the known HR-specific migration files.
     */
    private function isMigrationHrRelated(string $migrationName): bool
    {
        // Explicit HR migration keywords
        $hrKeywords = [
            'hr_', 'commission_type', 'commission_target', 'gratuity_setting',
            'commission_setting', 'document_setting', 'shift_type', 'attendance_status',
            'shift_attendance_rule', 'permission_tables', 'admins_table',
            'employees_table', 'employee_bank', 'employment_type', 'employee_status',
            'role_gratuity', 'role_commission', 'employee_attendance', 'employee_break',
            'daily_activity', 'role_activity', 'employee_payslip', 'payslip_item',
            'gratuity_balance', 'gratuity_payout', 'employee_daily_activity',
            'payroll', 'payslip', 'payroll_status', 'payroll_detail',
            'gratuity_payout_status', 'add_columns_payroll', 'add_columns_gratuity',
            'employee_document', 'holidays_table', 'employee_holiday', 'employee_working',
            'leave_type', 'employee_leave', 'employee_assign_leave',
            'payslip_item_type', 'payslip_adjustment', 'profile_path_employee',
            'break_duration', 'departments_table', 'designations_table',
            'department_id_payrolls', 'department_and_designation', 'commission_and_gratuity',
            'employee_status_histor', 'ticket_type', 'ticket_status', 'employee_ticket',
            'ticket_attachment', 'ticket_message', 'ticket_id_to_employee',
            'user_type_to_employee', 'employee_attendance_request', 'rejected_by',
            'valid_gratuity', 'tracking_columns', 'entry_weight', 'attendancecolumn',
            'tax_slab', 'tax_columns', 'petty_cash', 'agent_id_employee',
            'employee_account_type', 'account_type_id', 'overtime_to_employee',
            'user_screenshots', 'image_to_petty', 'total_amount_payroll',
            'employee_taxes', 'currency_rates',
        ];

        $lower = strtolower($migrationName);
        foreach ($hrKeywords as $keyword) {
            if (str_contains($lower, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
