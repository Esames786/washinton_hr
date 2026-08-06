<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hello Transport onboarding reference data — PRODUCTION-SAFE.
 *
 * Like DocumentSettingSeeder it only INSERTS what is missing and UPDATES what it owns; it never
 * truncates or deletes, so it is safe to run repeatedly on the shared live database:
 *
 *     php artisan db:seed --class=Database\\Seeders\\HelloOnboardingSeeder
 *
 * Requires migration 2026_08_02_000001_add_hello_onboarding_columns (adds hr_document_settings.brand).
 *
 * What it does
 *  1. Adds the Hello shift "Morning (10am – 5pm)" (10:00–17:00). CrazyRays keeps its own
 *     "Morning" 09:00–17:00 row untouched.
 *  2. Adds the "State ID" document type, required, Hello-only.
 *  3. Tags the existing Pakistan/CrazyRays-specific document types as brand = 'crazyrays' so a
 *     Hello applicant is never asked for a CNIC, rental agreement, workplace photos, etc.
 *     Resume + Experience Letter stay brand = NULL, i.e. they apply to BOTH brands.
 */
class HelloOnboardingSeeder extends Seeder
{
    /** Document types that apply to BOTH brands — left with brand = NULL. (Round-2: Hello also
     *  requires bank details, educational certificate, selfie, workplace + laptop pictures and
     *  current IP, so those moved from the CrazyRays-only list to shared.) */
    private const SHARED_DOCUMENT_IDS = [
        2,   // Educational Certificate (required)
        3,   // Experience Letter (optional)
        5,   // Bank Account Details (required)
        12,  // Resume (required)
        14,  // Selfie Collage (required)
        16,  // Workplace Pictures (required)
        19,  // laptop picture (required)
        20,  // Current Ip (required)
    ];

    /** Existing document types that only make sense for CrazyRays (Pakistan) staff. */
    private const CRAZYRAYS_DOCUMENT_IDS = [
        1,  // CNIC (National ID)
        4,  // Passport
        6,  // Medical Certificate
        7,  // Police Clearance
        8,  // smart card
        9,  // Bill (owned house)
        10, // CNIC Front
        11, // CNIC Back
        13, // Father/Mother CNIC
        15, // Address Verification Video
        17, // Rental Agreement
        18, // Rental agreement person CNIC (landlord)
    ];

    public function run(): void
    {
        $now = now();

        // ── 1. Hello shift: Morning (10am – 5pm) ─────────────────────────────
        DB::table('hr_shift_types')->updateOrInsert(
            ['name' => 'Morning (10am - 5pm)'],
            [
                'shift_start' => '10:00:00',
                'shift_end'   => '17:00:00',
                'status'      => 1,
                'updated_at'  => $now,
                'created_at'  => $now,
            ]
        );
        $this->command?->info('✔ Shift "Morning (10am - 5pm)" ready.');

        // ── 1b. Attendance rules for that shift ──────────────────────────────
        // Check-in hard-fails ("Shift Attendance rule not found for employee.") for any shift
        // without rows in hr_shift_attendance_rules — the admin shift screen does not create
        // them automatically. Mirrors shift 1 (Morning 09-17) shifted +1h for the 10:00 start.
        $shiftId = DB::table('hr_shift_types')->where('name', 'Morning (10am - 5pm)')->value('id');
        if ($shiftId && !DB::table('hr_shift_attendance_rules')->where('shift_type_id', $shiftId)->exists()) {
            $rules = [
                // [attendance_status_id, entry_time, entry_weight(deduct %)]
                [2,  '10:00:00', 0],   // on time
                [1,  '10:10:00', 80],
                [11, '11:55:00', 25],
                [9,  '12:00:00', 25],
                [10, '13:30:00', 25],
                [3,  '14:00:00', 50],
                [4,  '16:00:00', 75],
                [5,  null, 100],       // absent
                [6,  null, 0],
                [7,  null, 0],
                [8,  null, 0],
            ];
            foreach ($rules as [$statusId, $time, $weight]) {
                DB::table('hr_shift_attendance_rules')->insert([
                    'shift_type_id'        => $shiftId,
                    'attendance_status_id' => $statusId,
                    'entry_time'           => $time,
                    'entry_weight'         => $weight,
                    'status'               => 1,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
            $this->command?->info('✔ Attendance rules created for shift "Morning (10am - 5pm)".');
        }

        if (!Schema::hasColumn('hr_document_settings', 'brand')) {
            $this->command?->warn('! hr_document_settings.brand missing — run migrations first; skipping document brand setup.');
            return;
        }

        // ── 2. Hello document: State ID (required) ───────────────────────────
        DB::table('hr_document_settings')->updateOrInsert(
            ['title' => 'State ID'],
            [
                'is_required' => 1,
                'description' => 'Upload a clear photo/scan of your state-issued photo ID.',
                'status'      => 1,
                'max_files'   => 2,
                'file_kind'   => 'any',
                'condition'   => null,
                'brand'       => 'hellotransport',
                'updated_at'  => $now,
                'created_at'  => $now,
            ]
        );
        $this->command?->info('✔ Document type "State ID" ready (Hello, required).');

        // ── 3. Brand-tag the existing document types ─────────────────────────
        // Explicit id lists (not a blanket update) so any document an admin adds later keeps
        // brand = NULL and stays visible to both brands until it is deliberately tagged.
        $tagged = DB::table('hr_document_settings')
            ->whereIn('id', self::CRAZYRAYS_DOCUMENT_IDS)
            ->whereNull('brand')
            ->update(['brand' => 'crazyrays', 'updated_at' => $now]);

        DB::table('hr_document_settings')
            ->whereIn('id', self::SHARED_DOCUMENT_IDS)
            ->update(['brand' => null, 'updated_at' => $now]);

        $this->command?->info("✔ Tagged {$tagged} document type(s) as CrazyRays-only; Resume + Experience shared.");
    }
}
