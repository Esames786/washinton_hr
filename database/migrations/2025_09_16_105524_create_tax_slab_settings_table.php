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
        Schema::create('hr_tax_slab_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title',100); // Slab name
            $table->decimal('min_income', 15, 2)->default(0);
            $table->decimal('max_income', 15, 2)->nullable(); // null = unlimited
            $table->decimal('rate', 10, 2); // fixed amount or percentage
            $table->enum('type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('global_cap', 15, 2)->nullable(); // max limit
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('hr_tax_slab_settings');
    }
};
