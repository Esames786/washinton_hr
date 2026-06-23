<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_attendances', function (Blueprint $table) {
            $table->unsignedInteger('productive_seconds')->nullable()->after('working_hours');
            $table->decimal('productive_percent', 5, 2)->nullable()->after('productive_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_attendances', function (Blueprint $table) {
            $table->dropColumn(['productive_seconds', 'productive_percent']);
        });
    }
};
