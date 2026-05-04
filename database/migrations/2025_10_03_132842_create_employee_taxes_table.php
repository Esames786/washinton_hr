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
        Schema::create('hr_employee_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->date('initiate_date');
            $table->decimal('tax_amount',12,2)->default(0);
            $table->tinyInteger('status_id')->default(1);
            $table->unsignedInteger('payroll_id')->nullable();
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
        Schema::dropIfExists('hr_employee_taxes');
    }
};
