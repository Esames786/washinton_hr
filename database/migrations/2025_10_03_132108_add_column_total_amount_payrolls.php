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
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->decimal('total_basic_salary',12,2)->default(0)->after('payroll_date');
            $table->decimal('total_tax',12,2)->default(0)->after('total_basic_salary');
            $table->decimal('total_deduction',12,2)->default(0)->after('total_tax');
            $table->decimal('total_net_salary',12,2)->default(0)->after('total_deduction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
           $table->dropColumn('total_basic_salary');
           $table->dropColumn('total_tax');
           $table->dropColumn('total_deduction');
           $table->dropColumn('total_net_salary');
        });
    }
};
