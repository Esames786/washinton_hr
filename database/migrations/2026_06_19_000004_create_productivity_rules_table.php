<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productivity_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label');
            $table->decimal('min_percent', 5, 2)->default(0);   // inclusive lower bound (productive % of shift)
            $table->decimal('max_percent', 5, 2)->default(100); // upper bound (display only)
            $table->unsignedInteger('attendance_status_id');    // 2=Present,3=Half,9=Quarter,5=Absent
            $table->decimal('deduction_percent', 5, 2)->default(0); // salary deduction %
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // Seed default bands (matches confirmed defaults: 75/40/10% -> 1.0/0.5/0.3/0)
        $now = now();
        DB::table('productivity_rules')->insert([
            ['label' => 'Full Day',    'min_percent' => 75, 'max_percent' => 100, 'attendance_status_id' => 2, 'deduction_percent' => 0,   'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Half Day',    'min_percent' => 40, 'max_percent' => 75,  'attendance_status_id' => 3, 'deduction_percent' => 50,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Quarter Day', 'min_percent' => 10, 'max_percent' => 40,  'attendance_status_id' => 9, 'deduction_percent' => 70,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Absent',      'min_percent' => 0,  'max_percent' => 10,  'attendance_status_id' => 5, 'deduction_percent' => 100, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('productivity_rules');
    }
};
