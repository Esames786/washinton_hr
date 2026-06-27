<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_payroll_details', function (Blueprint $table) {
            $table->unsignedInteger('manual_productive_minutes')->nullable()->after('net_salary');
        });
    }

    public function down(): void
    {
        Schema::table('hr_payroll_details', function (Blueprint $table) {
            $table->dropColumn('manual_productive_minutes');
        });
    }
};
