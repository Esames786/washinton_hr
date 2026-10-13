<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase C — marks an employee as having come from the recruitment pipeline.
 *
 * Only employees created from a shortlisted application carry this. It is what tells
 * EmployeeObserver to hold back the CRM account until onboarding completes; anyone an
 * admin creates directly has it NULL and behaves exactly as before.
 *
 * Deliberately NOT a foreign key: cr_applications lives in the same database today, but
 * the two brands were split on 13 Oct and a hard constraint across a table that may move
 * is a liability. The link is resolved in code.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hr_employees', 'cr_application_id')) {
            return;
        }

        Schema::table('hr_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('cr_application_id')->nullable()->index()->after('agent_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('hr_employees', 'cr_application_id')) {
            return;
        }

        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn('cr_application_id');
        });
    }
};
