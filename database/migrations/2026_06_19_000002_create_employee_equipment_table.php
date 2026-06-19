<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeEquipmentTable extends Migration
{
    public function up()
    {
        Schema::create('employee_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('equipment_type_id');
            $table->string('asset_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('assigned_date');
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['assigned', 'returned'])->default('assigned');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('equipment_type_id')->references('id')->on('equipment_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_equipment');
    }
}
