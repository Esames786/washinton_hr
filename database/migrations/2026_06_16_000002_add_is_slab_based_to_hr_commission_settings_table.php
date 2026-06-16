<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_commission_settings', function (Blueprint $table) {
            $table->tinyInteger('is_slab_based')->default(0)->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('hr_commission_settings', function (Blueprint $table) {
            $table->dropColumn('is_slab_based');
        });
    }
};
