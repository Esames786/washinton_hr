<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * W-9 in the HR onboarding workflow (client, 23 Oct 2026). HR can send the W-9 before the agent
 * has a CRM account, so the "sent" flag lives on the HR record; the CRM reads it too (shared
 * database). user.w9_required stays for the CRM's own button.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hr_employees') && ! Schema::hasColumn('hr_employees', 'w9_required')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                $table->tinyInteger('w9_required')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hr_employees', 'w9_required')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                $table->dropColumn('w9_required');
            });
        }
    }
};
