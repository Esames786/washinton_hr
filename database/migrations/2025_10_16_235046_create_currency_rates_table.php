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
        Schema::create('hr_currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 10)->default('USD');
            $table->string('to_currency', 1015)->default('PKR');
            $table->decimal('rate', 15, 4)->default(0);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('hr_currency_rates');
    }
};
