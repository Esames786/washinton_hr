<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Item 10 — "Earning & Payouts" for subcontractors.
 *
 * Subcontractors are paid commission on revenue, not a salary, so the payslip needs
 * figures the salary model has no column for. These extend hr_payroll_details rather
 * than creating a parallel system: payroll generation, statuses and the payslip screens
 * all already hang off it, and `total_commission` is already there.
 *
 * Revenue is entered by an admin. It cannot be derived from the order table — checked
 * against live data: `paid_amount` is empty on all 268 orders, and the other price
 * columns are between 38% and 57% populated and inconsistent with each other. Computing
 * revenue from them would produce confident, wrong numbers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_payroll_details', function (Blueprint $table) {
            if (! Schema::hasColumn('hr_payroll_details', 'revenue_generated')) {
                $table->decimal('revenue_generated', 14, 2)->nullable()->after('basic_salary');
            }
            if (! Schema::hasColumn('hr_payroll_details', 'commission_rate')) {
                $table->decimal('commission_rate', 6, 2)->nullable()->after('revenue_generated');
            }
            if (! Schema::hasColumn('hr_payroll_details', 'payment_method')) {
                $table->string('payment_method', 40)->nullable()->after('net_salary');
            }
            if (! Schema::hasColumn('hr_payroll_details', 'payment_reference')) {
                $table->string('payment_reference', 60)->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('hr_payroll_details', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_payroll_details', function (Blueprint $table) {
            foreach (['revenue_generated', 'commission_rate', 'payment_method', 'payment_reference', 'paid_at'] as $c) {
                if (Schema::hasColumn('hr_payroll_details', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
