<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * #18/#19: onboarding (pre-Active) employee statuses must read as "Pending" so a
 * subcontractor always sees a pending state until their account is fully Active.
 *   7  Document Verification → Documents Verification Pending
 *   9  Management Approval    → Management Approval Pending
 * (8 "Pending Contract" already carries "Pending".) Idempotent by id.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hr_employee_statuses')->where('id', 7)->update(['name' => 'Documents Verification Pending']);
        DB::table('hr_employee_statuses')->where('id', 9)->update(['name' => 'Management Approval Pending']);
    }

    public function down(): void
    {
        DB::table('hr_employee_statuses')->where('id', 7)->update(['name' => 'Document Verification']);
        DB::table('hr_employee_statuses')->where('id', 9)->update(['name' => 'Management Approval']);
    }
};
