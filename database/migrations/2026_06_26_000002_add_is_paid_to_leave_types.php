<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_leave_types', function (Blueprint $table) {
            $table->tinyInteger('is_paid')->default(1)->after('description'); // 1 = Paid, 0 = Unpaid
        });

        // Existing default types: "Unpaid Leave" -> unpaid, rest paid
        \Illuminate\Support\Facades\DB::table('hr_leave_types')->where('name', 'like', '%Unpaid%')->update(['is_paid' => 0]);
    }

    public function down(): void
    {
        Schema::table('hr_leave_types', function (Blueprint $table) {
            $table->dropColumn('is_paid');
        });
    }
};
