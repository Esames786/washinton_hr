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
        Schema::table('hr_employee_tickets', function (Blueprint $table) {
            $table->date('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');;
            $table->date('rejected_at')->nullable()->after('rejected_by');;

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_employee_tickets', function (Blueprint $table) {
           $table->dropColumn('approved_at');
           $table->dropColumn('rejected_by');
           $table->dropColumn('rejected_at');
        });
    }
};
