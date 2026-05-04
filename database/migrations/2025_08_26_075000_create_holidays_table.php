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
        Schema::create('hr_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name',150);
            $table->date('holiday_date')->nullable(); // one-time holidays
            $table->boolean('is_recurring')->default(0); // 1 = repeat every year
            $table->unsignedTinyInteger('month')->nullable(); // recurring month
            $table->unsignedTinyInteger('day')->nullable();   // recurring day
            $table->tinyInteger('status')->default(1); // 1=Active, 0=Inactive
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
        Schema::dropIfExists('hr_holidays');
    }
};
