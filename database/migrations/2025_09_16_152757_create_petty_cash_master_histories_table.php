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
        Schema::create('hr_petty_cash_master_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained('hr_petty_cash_masters')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('action', ['opening', 'add', 'deduct']);
            $table->string('description')->nullable();
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
        Schema::dropIfExists('hr_petty_cash_master_histories');
    }
};
