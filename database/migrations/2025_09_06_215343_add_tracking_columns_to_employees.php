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
            $table->timestamp('last_seen_at')->nullable()->after('commission_id');
            $table->boolean('is_logged_in')->default(0)->after('last_seen_at');
            $table->date('login_at')->nullable()->after('is_logged_in');


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
            $table->dropColumn('last_seen_at');;
            $table->dropColumn('is_logged_in');
            $table->dropColumn('login_at');
        });
    }
};
