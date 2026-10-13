<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase B — the five onboarding statuses the recruitment pipeline needs.
 *
 * Purely additive. Adding a status changes nothing until something sets it, so no
 * existing employee is affected by this migration.
 *
 * Three of the client's seven statuses already exist and are deliberately NOT duplicated:
 *   Verification in Progress  -> "Documents Verification Pending" (id 7)
 *   Management Approval       -> "Management Approval Pending"    (id 9)
 *   Contract Pending          -> "Pending Contract"               (id 8)
 *   Active                    -> "Active"                         (id 1)
 *
 * Note "Documents Pending" is NOT the same as id 7: this one means the agent has not
 * uploaded yet, id 7 means they have and it awaits verification.
 */
return new class extends Migration
{
    /** Order matters only for display; lookups are by name. */
    private const STATUSES = [
        'NDA Pending',
        'Questionnaire Pending',
        'In Progress',
        'Documents Pending',
        'Onboarding',
    ];

    public function up(): void
    {
        foreach (self::STATUSES as $name) {
            $exists = DB::table('hr_employee_statuses')->where('name', $name)->exists();
            if ($exists) {
                continue;
            }

            DB::table('hr_employee_statuses')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Only remove them if nothing is using them — never orphan an employee's status.
        foreach (self::STATUSES as $name) {
            $row = DB::table('hr_employee_statuses')->where('name', $name)->first();
            if (! $row) {
                continue;
            }

            $inUse = DB::table('hr_employees')->where('employee_status_id', $row->id)->exists();
            if ($inUse) {
                continue;
            }

            DB::table('hr_employee_statuses')->where('id', $row->id)->delete();
        }
    }
};
