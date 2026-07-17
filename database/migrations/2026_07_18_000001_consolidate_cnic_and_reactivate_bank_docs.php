<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * #15 — CNIC was asked 3 times (#1 National ID, #10 Front, #11 Back). Consolidate to a
 *       single document (#10, retitled) that takes one PDF/image with both sides.
 * #6  — Reactivate "Bank Account Details" (#5) in the required-documents set.
 * Idempotent (keyed by id). Deactivating a setting never deletes already-uploaded files.
 */
return new class extends Migration
{
    public function up(): void
    {
        // #15 — single CNIC document (front & back in one file).
        DB::table('hr_document_settings')->where('id', 10)->update([
            'title'       => 'CNIC (Front & Back)',
            'description' => 'Upload a single PDF or image containing BOTH the front and back of your CNIC.',
            'is_required' => 1,
            'status'      => 1,
        ]);
        // Deactivate the now-redundant CNIC entries.
        DB::table('hr_document_settings')->whereIn('id', [1, 11])->update(['status' => 0]);

        // #6 — reactivate Bank Account Details.
        DB::table('hr_document_settings')->where('id', 5)->update(['status' => 1]);
    }

    public function down(): void
    {
        DB::table('hr_document_settings')->where('id', 10)->update([
            'title'       => 'CNIC Front',
            'description' => null,
            'is_required' => 0,
        ]);
        DB::table('hr_document_settings')->whereIn('id', [1, 11])->update(['status' => 1]);
        DB::table('hr_document_settings')->where('id', 5)->update(['status' => 0]);
    }
};
