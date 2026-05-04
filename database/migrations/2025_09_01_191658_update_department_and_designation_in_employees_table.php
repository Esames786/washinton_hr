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
            // Drop the old string columns
            $table->dropColumn(['department', 'designation']);

            // Add the new foreign key columns
            $table->unsignedBigInteger('department_id')->nullable()->after('employee_code');
            $table->unsignedBigInteger('designation_id')->nullable()->after('department_id');
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
           $table->dropColumn(['department_id', 'designation_id']);

            // Add back the old string columns
            $table->string('department')->nullable()->after('employee_code');
            $table->string('designation')->nullable()->after('department');

        });
    }
};
