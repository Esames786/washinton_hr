<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * #4 (meeting 1 Aug): the contract is now e-SIGNED like the NDA — the subcontractor draws a
 * signature before accepting. Stored with the IP it was signed from. Guarded/idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employees', 'contract_signature')) {
                $table->longText('contract_signature')->nullable()->after('contract_accepted_at');
            }
            if (!Schema::hasColumn('hr_employees', 'contract_signed_ip')) {
                $table->string('contract_signed_ip', 45)->nullable()->after('contract_signature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            foreach (['contract_signature', 'contract_signed_ip'] as $col) {
                if (Schema::hasColumn('hr_employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
