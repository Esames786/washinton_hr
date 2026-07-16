<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #21: distinguish in-house employees from subcontractors.
 * Subcontractors get NO Leaves / Gratuity / Tax (only in-house do).
 * Existing rows default to 'inhouse' so their current benefit data is preserved;
 * CrazyRays bridge signups and admin-marked records become 'subcontractor'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employees', 'worker_type')) {
                $table->enum('worker_type', ['inhouse', 'subcontractor'])
                      ->default('inhouse')
                      ->after('employment_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (Schema::hasColumn('hr_employees', 'worker_type')) {
                $table->dropColumn('worker_type');
            }
        });
    }
};
