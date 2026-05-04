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
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->tinyInteger('is_taxable')->default(0)->after('commission_id');
            $table->unsignedInteger('tax_slab_setting_id')->nullable()->after('is_taxable');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn('is_taxable');
            $table->dropColumn('tax_slab_setting_id');
        });
    }
};
