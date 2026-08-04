<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hello Transport onboarding support.
 *
 * Deliberately minimal — everything that already had a home reuses the existing column:
 *   State ID          -> hr_employees.cnic          (existing)
 *   Father / Mother   -> father_name / mother_name  (existing)
 *   Street / City / State -> address / city / state (existing)
 *   Shift             -> shift_id                   (existing)
 *   Pay type          -> account_type_id = 2 "Commission Only" (existing)
 *   Phone (US code)   -> phone                      (existing)
 *
 * Only two genuinely new fields are added here:
 *   zip      — US postal code (no existing column; city/state exist but zip did not)
 *   timezone — per-person timezone. CrazyRays staff stay Asia/Karachi (the default), Hello
 *              agents choose theirs at signup; it drives their check-in/out, breaks and
 *              attendance marking as well as displayed times.
 *
 * Plus a brand dimension on document settings: the existing `condition` column already means
 * house-ownership (own/rent), so brand needs its own column rather than overloading that one.
 *
 * All guarded so the migration is safe to re-run on the shared production database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employees', 'zip')) {
                $table->string('zip', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('hr_employees', 'timezone')) {
                $table->string('timezone', 64)->nullable()->default('Asia/Karachi')->after('country');
            }
        });

        Schema::table('hr_document_settings', function (Blueprint $table) {
            // NULL = applies to every brand. 'hellotransport' / 'crazyrays' = that brand only.
            if (!Schema::hasColumn('hr_document_settings', 'brand')) {
                $table->string('brand', 32)->nullable()->after('condition');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            foreach (['zip', 'timezone'] as $col) {
                if (Schema::hasColumn('hr_employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('hr_document_settings', function (Blueprint $table) {
            if (Schema::hasColumn('hr_document_settings', 'brand')) {
                $table->dropColumn('brand');
            }
        });
    }
};
