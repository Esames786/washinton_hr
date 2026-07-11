<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * B6: Curate the subcontractor document checklist.
 *
 * Client wants only these documents active on the HR onboarding form:
 *   Resume, CNIC, Experience Letter, Last education certificate,
 *   Utility Bill (address proof), Father/Mother CNIC.
 * Everything else is soft-disabled (status = 0) so no historic data is lost.
 *
 * Existing rows (hr_document_settings) at time of writing:
 *   1  CNIC (National ID)      9  Bill (utility)
 *   2  Educational Certificate 10 CNIC Front
 *   3  Experience Letter       11 CNIC Back
 *   4  Passport                12 Resume
 *   5  Bank Account Details
 *   6  Medical Certificate
 *   7  Police Clearance
 *   8  smart card
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hr_document_settings')) {
            return;
        }

        // Activate the wanted documents (only if the row still exists).
        DB::table('hr_document_settings')
            ->whereIn('id', [1, 2, 3, 9, 10, 11, 12])
            ->update(['status' => 1, 'updated_at' => now()]);

        // Soft-disable everything the client does not want on the form.
        DB::table('hr_document_settings')
            ->whereIn('id', [4, 5, 6, 7, 8])
            ->update(['status' => 0, 'updated_at' => now()]);

        // Ensure the new "Father/Mother CNIC" document exists and is active.
        $exists = DB::table('hr_document_settings')
            ->whereRaw('LOWER(title) = ?', ['father/mother cnic'])
            ->exists();

        if (!$exists) {
            DB::table('hr_document_settings')->insert([
                'title'       => 'Father/Mother CNIC',
                'is_required' => 0,
                'description' => 'CNIC of father or mother (next of kin proof)',
                'input_type'  => 'file',
                'status'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } else {
            DB::table('hr_document_settings')
                ->whereRaw('LOWER(title) = ?', ['father/mother cnic'])
                ->update(['status' => 1, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // No-op: this is a curation of data, not a schema change.
        // Reverting active/inactive flags automatically could clobber
        // admin edits made after deploy, so we leave the data as-is.
    }
};
