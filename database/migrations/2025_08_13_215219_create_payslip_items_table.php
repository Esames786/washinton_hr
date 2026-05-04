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
        Schema::create('hr_payslip_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_detail_id');
            $table->integer('item_type_id');
            $table->string('description');
            $table->decimal('amount', 12, 2)->default(0.00);
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
        Schema::dropIfExists('hr_payslip_items');
    }
};
