<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Canonical hr_document_settings — PRODUCTION-SAFE.
 *
 * Uses updateOrInsert keyed by id: it only UPDATES existing rows and INSERTS missing
 * ones. It NEVER truncates or deletes, so running it on production cannot lose data or
 * orphan employee_documents. Run any time to (re)sync document types:
 *
 *     php artisan db:seed --class=Database\\Seeders\\DocumentSettingSeeder
 *
 * Requires the P3 columns (max_files, file_kind, condition) — i.e. run migrations first.
 */
class DocumentSettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // [id, title, is_required, description, status, max_files, file_kind, condition]
        $rows = [
            [1,  'CNIC (National ID)',          1, 'Computerized National Identity Card',            0, 1, 'any',   null],  // #15: merged into #10
            [2,  'Educational Certificate',     1, 'Highest degree or diploma',                      1, 1, 'any',   null],
            [3,  'Experience Letter',           0, 'Previous employer experience letter',            1, 1, 'any',   null],
            [4,  'Passport',                    0, 'Valid passport copy',                            0, 1, 'any',   null],
            [5,  'Bank Account Details',        1, 'Bank account verification document',             1, 1, 'any',   null],  // #6
            [6,  'Medical Certificate',         0, 'Fitness certificate from doctor',                0, 1, 'any',   null],
            [7,  'Police Clearance',            0, 'Character certificate from police',              0, 1, 'any',   null],
            [8,  'smart card',                  0, 'Smart card',                                     0, 1, 'any',   null],
            [9,  'Bill',                        0, 'Utility bill (owned house, if CNIC address differs)', 1, 1, 'any', 'own'],  // #7
            [10, 'CNIC Front',                  1, 'Upload a clear photo/scan of the FRONT side of your CNIC.', 1, 1, 'any', null],
            [11, 'CNIC Back',                   1, 'Upload a clear photo/scan of the BACK side of your CNIC.',  1, 1, 'any', null],
            [12, 'Resume',                      1, 'Updated CV / resume',                            1, 1, 'any',   null],
            [13, 'Father/Mother CNIC',          1, "Father's or Mother's CNIC",                      1, 1, 'any',   null],
            [14, 'Selfie (4 angles)',           1, 'Up to 4 selfies from different angles, plain blue/white background.', 1, 4, 'image', null],  // #4
            [15, 'Address Verification Video',  1, '~15s video from the street to your house entrance.', 1, 1, 'video', null], // #5
            [16, 'Workplace Pictures',          1, 'Photos of your workplace / work area (up to 4).', 1, 4, 'image', null],  // #8
            [17, 'Rental Agreement',            1, 'Required for rented houses.',                    1, 1, 'any',   'rent'], // #3
            [18, 'Landlord CNIC',               1, "The house owner's / landlord's CNIC (rented houses).", 1, 1, 'any', 'rent'], // #3
        ];

        foreach ($rows as [$id, $title, $req, $desc, $status, $max, $kind, $cond]) {
            $data = [
                'title'       => $title,
                'is_required' => $req,
                'description' => $desc,
                'input_type'  => 'file',
                'max_files'   => $max,
                'file_kind'   => $kind,
                'condition'   => $cond,
                'status'      => $status,
                'updated_at'  => $now,
            ];

            // UPDATE existing (keeps created_at + any FK links) / INSERT missing only. Never deletes.
            if (DB::table('hr_document_settings')->where('id', $id)->exists()) {
                DB::table('hr_document_settings')->where('id', $id)->update($data);
            } else {
                DB::table('hr_document_settings')->insert(array_merge(['id' => $id, 'created_at' => $now], $data));
            }
        }

        $this->command?->info('✓ Document settings synced (production-safe, no data lost).');
    }
}
