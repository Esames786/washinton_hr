<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_commission_slabs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_setting_id');
            $table->decimal('profit_from', 12, 2)->default(0);
            $table->decimal('profit_to', 12, 2)->nullable();   // null = no upper limit (open-ended top slab)
            $table->decimal('value', 12, 2)->default(0);       // rate % if type=1, flat PKR if type=2
            $table->timestamps();

            $table->foreign('commission_setting_id')
                  ->references('id')->on('hr_commission_settings')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_commission_slabs');
    }
};
