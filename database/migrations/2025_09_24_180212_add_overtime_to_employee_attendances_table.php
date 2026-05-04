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
            $table->string('overtime_seconds')->nullable()->after('working_hours');
            $table->decimal('overtime_amount', 15, 2)->default(0)->after('overtime_seconds');
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
            $table->dropColumn(['overtime_seconds', 'overtime_amount']);
        });
    }
};
