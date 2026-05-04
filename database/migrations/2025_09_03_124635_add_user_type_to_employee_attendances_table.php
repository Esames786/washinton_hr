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
        Schema::table('hr_employee_attendances', function (Blueprint $table) {
            $table->tinyInteger('user_type')->nullable()->after('ticket_id'); //1-admin or 2-employee
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_employee_attendances', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};
