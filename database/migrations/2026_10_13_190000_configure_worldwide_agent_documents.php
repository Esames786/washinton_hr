<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The worldwide agent document set (client email, 8 Oct, items 1-8).
 *
 * The document system is already config-driven, so this is data rather than code: it
 * adjusts hr_document_settings so the required set matches what a worldwide agent can
 * actually provide, instead of assuming a Pakistani CNIC or a US state ID.
 *
 * Client's list, and where each lands:
 *   1 Government-issued photo ID   -> widens the existing "State ID" row (34 live uploads)
 *   2 Proof of address             -> new, optional ("only if required")
 *   3 Tax identification           -> new, required
 *   4 Payment / bank details       -> existing "Bank Account Details", already required
 *   5 Extra country tax forms      -> new, optional, up to 3 files
 *   6 Selfie                       -> existing selfie row, made required
 *   7 Experience letter            -> existing row, already optional
 *   8 Workplace setup              -> existing workplace + laptop rows, descriptions fixed
 *
 * HELLO ONLY. CrazyRays keeps its CNIC-based set, and since the brands are on separate
 * databases this migration must not be run against the CrazyRays one.
 */
return new class extends Migration
{
    private const BRAND = 'hellotransport';

    public function up(): void
    {
        if (config('brands.force') === 'crazyrays') {
            return;   // CrazyRays keeps its own document set
        }

        // 1 — the ID is no longer US-specific.
        DB::table('hr_document_settings')
            ->where('title', 'State ID')
            ->update([
                'title'       => 'Government-Issued Photo ID',
                'description' => 'Passport, national ID card or equivalent government-issued photo ID. '
                               . 'Upload both sides if your ID has them.',
                'is_required' => 1,
                'max_files'   => 2,
                'status'      => 1,
                'updated_at'  => now(),
            ]);

        // 6 — a selfie is required for identity verification.
        DB::table('hr_document_settings')
            ->where('title', 'like', 'Selfie%')
            ->update(['is_required' => 1, 'status' => 1, 'updated_at' => now()]);

        // 8 — the workplace photos, spelled out. These rows existed but one description
        // was literally "asd", so nobody could tell what to upload.
        DB::table('hr_document_settings')
            ->where('title', 'like', 'Workplace Picture%')
            ->update([
                'description' => 'Photos of your working setup: your workstation, your internet / router, '
                               . 'and the general working environment. Up to 4 photos.',
                'max_files'   => 4,
                'is_required' => 1,
                'status'      => 1,
                'updated_at'  => now(),
            ]);

        DB::table('hr_document_settings')
            ->where('title', 'like', 'laptop picture%')
            ->update([
                'title'       => 'Computer / Laptop Setup',
                'description' => 'A photo of the computer or laptop you will be working on.',
                'is_required' => 1,
                'status'      => 1,
                'updated_at'  => now(),
            ]);

        // 2, 3, 5 — new, and country-dependent by nature.
        $new = [
            [
                'title'       => 'Proof of Address',
                'description' => 'A utility bill, bank statement or tenancy document showing your name '
                               . 'and address. Only required if we ask for it.',
                'is_required' => 0,
                'max_files'   => 1,
            ],
            [
                'title'       => 'Tax Identification Document',
                'description' => 'Your tax identification for the country you live in — for example a W-9 '
                               . '(US), NTN (Pakistan), UTR (UK), TIN or local equivalent.',
                'is_required' => 1,
                'max_files'   => 1,
            ],
            [
                'title'       => 'Additional Tax / Payment Forms',
                'description' => 'Any further tax or payment paperwork your country requires before we can '
                               . 'pay you. Leave blank if none apply.',
                'is_required' => 0,
                'max_files'   => 3,
            ],
        ];

        foreach ($new as $doc) {
            $exists = DB::table('hr_document_settings')->where('title', $doc['title'])->exists();
            if ($exists) {
                continue;
            }

            DB::table('hr_document_settings')->insert($doc + [
                'input_type' => 'file',
                'file_kind'  => 'any',
                'condition'  => null,
                'brand'      => self::BRAND,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Only remove the rows this migration created, and only if nothing was uploaded
        // against them — an employee's document must never be orphaned.
        foreach (['Proof of Address', 'Tax Identification Document', 'Additional Tax / Payment Forms'] as $title) {
            $row = DB::table('hr_document_settings')->where('title', $title)->first();
            if (! $row) {
                continue;
            }

            $used = DB::table('hr_employee_documents')->where('document_setting_id', $row->id)->exists();
            if ($used) {
                continue;
            }

            DB::table('hr_document_settings')->where('id', $row->id)->delete();
        }

        // The widened ID row keeps its uploads; only the label goes back.
        DB::table('hr_document_settings')
            ->where('title', 'Government-Issued Photo ID')
            ->update(['title' => 'State ID', 'is_required' => 0, 'updated_at' => now()]);
    }
};
