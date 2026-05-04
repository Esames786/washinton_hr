<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hr_employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employee_attendances', 'deducted_salary')) {
                $table->decimal('deducted_salary', 10, 2)
                    ->default(0)
                    ->after('calculated_salary');
            }

            if (!Schema::hasColumn('hr_employee_attendances', 'is_early_exit')) {
                $table->boolean('is_early_exit')
                    ->default(false)
                    ->after('deducted_salary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_employee_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('hr_employee_attendances', 'deducted_salary')) {
                $table->dropColumn('deducted_salary');
            }

            if (Schema::hasColumn('hr_employee_attendances', 'is_early_exit')) {
                $table->dropColumn('is_early_exit');
            }
        });
    }
};
