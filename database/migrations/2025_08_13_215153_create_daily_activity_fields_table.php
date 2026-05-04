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
        Schema::create('hr_daily_activity_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('field_type', ['text','textarea','number','date','time','select','file']);
            $table->text('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('hr_daily_activity_fields');
    }
};
