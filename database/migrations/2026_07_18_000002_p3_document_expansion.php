<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P3 — subcontractor document/verification expansion (profile screen).
 *  #4 multi-image per document (Selfie, max 4)          -> max_files + file_kind
 *  #5 15-sec address verification video                  -> Address Video (video)
 *  #8/#9 workplace pictures + working-equipment details  -> Workplace Pictures + hr_employee_work_equipment
 *  #3/#7 own vs rented conditional documents             -> condition column + house_ownership
 * Additive & idempotent. Existing documents/flow untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_document_settings', function (Blueprint $t) {
            if (!Schema::hasColumn('hr_document_settings', 'max_files')) {
                $t->unsignedTinyInteger('max_files')->default(1)->after('input_type');
            }
            if (!Schema::hasColumn('hr_document_settings', 'file_kind')) {
                $t->string('file_kind', 20)->default('any')->after('max_files'); // any | image | video
            }
            if (!Schema::hasColumn('hr_document_settings', 'condition')) {
                $t->string('condition', 20)->nullable()->after('file_kind'); // null | own | rent
            }
        });

        Schema::table('hr_employees', function (Blueprint $t) {
            if (!Schema::hasColumn('hr_employees', 'house_ownership')) {
                $t->string('house_ownership', 10)->nullable()->after('address'); // own | rent
            }
        });

        if (!Schema::hasTable('hr_employee_work_equipment')) {
            Schema::create('hr_employee_work_equipment', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('employee_id')->index();
                $t->string('name');
                $t->string('details')->nullable();
                $t->timestamps();
            });
        }

        // New document types (idempotent by title). Conditional docs are gated in the
        // profile by the employee's house_ownership, not by the global active flag.
        $docs = [
            ['title' => 'Selfie (4 angles)',        'description' => 'Upload up to 4 clear selfies from different angles with a plain blue or white background.', 'is_required' => 1, 'status' => 1, 'max_files' => 4, 'file_kind' => 'image', 'condition' => null],
            ['title' => 'Address Verification Video','description' => 'A ~15 second video walking from the street to your house entrance for address verification.',   'is_required' => 1, 'status' => 1, 'max_files' => 1, 'file_kind' => 'video', 'condition' => null],
            ['title' => 'Workplace Pictures',        'description' => 'Photos of your workplace / work area (up to 4).',                                              'is_required' => 1, 'status' => 1, 'max_files' => 4, 'file_kind' => 'image', 'condition' => null],
            ['title' => 'Rental Agreement',          'description' => 'Required if you live in a rented house.',                                                      'is_required' => 1, 'status' => 1, 'max_files' => 1, 'file_kind' => 'any',   'condition' => 'rent'],
            ['title' => 'Landlord CNIC',             'description' => "The house owner's / landlord's CNIC (rented houses).",                                          'is_required' => 1, 'status' => 1, 'max_files' => 1, 'file_kind' => 'any',   'condition' => 'rent'],
        ];
        foreach ($docs as $d) {
            $exists = DB::table('hr_document_settings')->where('title', $d['title'])->exists();
            if (!$exists) {
                DB::table('hr_document_settings')->insert(array_merge($d, [
                    'input_type' => 'file', 'created_at' => now(), 'updated_at' => now(),
                ]));
            }
        }

        // #7: existing "Bill" (#9) is for OWN houses whose CNIC address doesn't match.
        DB::table('hr_document_settings')->where('id', 9)->update(['condition' => 'own', 'is_required' => 0]);
    }

    public function down(): void
    {
        DB::table('hr_document_settings')->whereIn('title', [
            'Selfie (4 angles)', 'Address Verification Video', 'Workplace Pictures', 'Rental Agreement', 'Landlord CNIC',
        ])->delete();
        DB::table('hr_document_settings')->where('id', 9)->update(['condition' => null]);

        Schema::table('hr_document_settings', function (Blueprint $t) {
            foreach (['max_files', 'file_kind', 'condition'] as $c) {
                if (Schema::hasColumn('hr_document_settings', $c)) $t->dropColumn($c);
            }
        });
        Schema::table('hr_employees', function (Blueprint $t) {
            if (Schema::hasColumn('hr_employees', 'house_ownership')) $t->dropColumn('house_ownership');
        });
        Schema::dropIfExists('hr_employee_work_equipment');
    }
};
