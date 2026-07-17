<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Employment-split backfill. Existing HR employees all originated from CrazyRays
 * campaign signups (agent_id set) — i.e. Work From Home subcontractors under the
 * new model.
 *
 * PAYROLL-SAFE: only NOT-yet-active (onboarding) records are flipped to
 * 'subcontractor'. Already-Active employees keep their current worker_type so
 * their running payroll (gratuity/tax setup) is never disturbed; the client can
 * re-flag those manually if required. Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hr_employees')
            ->whereNotNull('agent_id')
            ->where('employee_status_id', '!=', 1) // 1 = Active
            ->where('worker_type', '!=', 'subcontractor')
            ->update(['worker_type' => 'subcontractor']);
    }

    public function down(): void
    {
        // Not reversible per-record (original value not stored); no-op.
    }
};
