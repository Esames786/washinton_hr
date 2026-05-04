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
        Schema::table('hr_gratuity_balances', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->after('closing_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_gratuity_balances', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
