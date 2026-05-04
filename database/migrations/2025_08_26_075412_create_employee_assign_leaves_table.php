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
        Schema::create('hr_employee_assign_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->integer('assigned_quota')->default(0); // e.g. 12 days
            $table->date('valid_from'); // e.g. 2025-06-01
            $table->date('valid_to');   // e.g. 2025-07-31
            $table->integer('used_quota')->default(0); // track how much already used
            $table->tinyInteger('status')->default(1); // 1 = Active, 0 = Expired
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_employee_assign_leaves');
    }
};
