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
            $table->unsignedBigInteger('gratuity_id')->nullable()->after('shift_id');
            $table->unsignedBigInteger('commission_id')->nullable()->after('gratuity_id');
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
            $table->dropColumn(['commission_id', 'gratuity_id']);
        });
    }
};
