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
            $table->dropColumn(['first_name', 'last_name']); // drop old columns
            $table->string('full_name')->after('id'); // ya jahan bhi chaiye
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
            $table->dropColumn('full_name');
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
        });
    }
};
