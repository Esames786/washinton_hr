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
        Schema::create('hr_shift_attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('shift_type_id');
            $table->unsignedInteger('attendance_status_id');
            $table->time('entry_time')->nullable();
            $table->decimal('entry_weight', 4, 2)->nullable(); // e.g., 0.3, 0.5, 1.0
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('hr_shift_attendance_rules');
    }
};
