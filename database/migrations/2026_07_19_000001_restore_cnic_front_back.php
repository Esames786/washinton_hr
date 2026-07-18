<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Restore the older, working CNIC flow: two separate uploads — #10 CNIC Front and
 * #11 CNIC Back — instead of the single consolidated "CNIC (Front & Back)" document
 * added by #15 (2026_07_18_000001). Applicants have two photos; asking for both
 * separately is clearer and matches how the form worked before.
 *
 * Idempotent (keyed by id). Never deletes already-uploaded files.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hr_document_settings')->where('id', 10)->update([
            'title'       => 'CNIC Front',
            'description' => 'Upload a clear photo/scan of the FRONT side of your CNIC.',
            'is_required' => 1,
            'status'      => 1,
            'max_files'   => 1,
            'file_kind'   => 'any',
        ]);
        DB::table('hr_document_settings')->where('id', 11)->update([
            'title'       => 'CNIC Back',
            'description' => 'Upload a clear photo/scan of the BACK side of your CNIC.',
            'is_required' => 1,
            'status'      => 1,
            'max_files'   => 1,
            'file_kind'   => 'any',
        ]);
    }

    public function down(): void
    {
        // Revert to the consolidated single-CNIC document (#15 behaviour).
        DB::table('hr_document_settings')->where('id', 10)->update([
            'title'       => 'CNIC (Front & Back)',
            'description' => 'Upload a single PDF or image containing BOTH the front and back of your CNIC.',
            'is_required' => 1,
            'status'      => 1,
        ]);
        DB::table('hr_document_settings')->where('id', 11)->update(['status' => 0]);
    }
};
