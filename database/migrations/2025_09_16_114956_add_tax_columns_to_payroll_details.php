<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_payroll_details', function (Blueprint $table) {
            $table->decimal('tax_amount',12,2)->default(0)->after('company_gratuity');
            $table->unsignedInteger('tax_slab_setting_id')->nullable()->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_payroll_details', function (Blueprint $table) {
            $table->dropColumn('tax_amount');
            $table->dropColumn('tax_slab_setting_id');
        });
    }
};
