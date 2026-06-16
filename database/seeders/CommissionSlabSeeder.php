<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissionSlabSeeder extends Seeder
{
    /**
     * Adds two slab-based commission settings (percentage + fixed) to production.
     * Existing commission settings and employee commission_id assignments are NOT touched.
     * Safe to re-run — skips if titles already exist.
     */
    public function run(): void
    {
        $now = now();

        // -------------------------------------------------------
        // 1. PERCENTAGE SLAB — Sales Commission Plan from policy doc
        //    Profit in USD → matching tier rate applied to PKR profit
        // -------------------------------------------------------
        if (!DB::table('hr_commission_settings')->where('title', 'Sales Commission Plan (Slab %)')->exists()) {

            $pctId = DB::table('hr_commission_settings')->insertGetId([
                'title'              => 'Sales Commission Plan (Slab %)',
                'description'        => 'Slab-based percentage commission. Rate depends on total USD profit for the payroll period.',
                'commission_type_id' => 1,   // Percentage
                'value'              => 0,   // not used for slab-based
                'is_slab_based'      => 1,
                'target_type_id'     => 1,   // Monthly
                'status'             => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            // 10 tiers from the commission policy document (profit in USD)
            $pctSlabs = [
                ['profit_from' => 100,  'profit_to' => 500,  'value' => 10],
                ['profit_from' => 501,  'profit_to' => 1000, 'value' => 15],
                ['profit_from' => 1001, 'profit_to' => 1500, 'value' => 20],
                ['profit_from' => 1501, 'profit_to' => 2000, 'value' => 25],
                ['profit_from' => 2001, 'profit_to' => 2500, 'value' => 30],
                ['profit_from' => 2501, 'profit_to' => 3000, 'value' => 35],
                ['profit_from' => 3001, 'profit_to' => 3500, 'value' => 40],
                ['profit_from' => 3501, 'profit_to' => 4000, 'value' => 45],
                ['profit_from' => 4001, 'profit_to' => 4500, 'value' => 50],
                ['profit_from' => 4501, 'profit_to' => 5000, 'value' => 55],
            ];

            foreach ($pctSlabs as $slab) {
                DB::table('hr_commission_slabs')->insert([
                    'commission_setting_id' => $pctId,
                    'profit_from'           => $slab['profit_from'],
                    'profit_to'             => $slab['profit_to'],
                    'value'                 => $slab['value'],
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }

            $this->command->info("Created: Sales Commission Plan (Slab %) — ID {$pctId} with 10 tiers.");
        } else {
            $this->command->warn('Skipped: Sales Commission Plan (Slab %) already exists.');
        }

        // -------------------------------------------------------
        // 2. FIXED SLAB — flat PKR amount per profit bracket
        //    Profit in USD → matching tier returns flat PKR amount
        // -------------------------------------------------------
        if (!DB::table('hr_commission_settings')->where('title', 'Sales Commission Plan (Slab Fixed)')->exists()) {

            $fixedId = DB::table('hr_commission_settings')->insertGetId([
                'title'              => 'Sales Commission Plan (Slab Fixed)',
                'description'        => 'Slab-based fixed PKR commission. Amount depends on total USD profit for the payroll period.',
                'commission_type_id' => 2,   // Fixed
                'value'              => 0,   // not used for slab-based
                'is_slab_based'      => 1,
                'target_type_id'     => 1,   // Monthly
                'status'             => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            // Example fixed PKR tiers (update via admin UI as needed)
            $fixedSlabs = [
                ['profit_from' => 100,  'profit_to' => 500,  'value' => 3000],
                ['profit_from' => 501,  'profit_to' => 1000, 'value' => 6000],
                ['profit_from' => 1001, 'profit_to' => 1500, 'value' => 10000],
                ['profit_from' => 1501, 'profit_to' => 2000, 'value' => 15000],
                ['profit_from' => 2001, 'profit_to' => 3000, 'value' => 20000],
                ['profit_from' => 3001, 'profit_to' => 5000, 'value' => 30000],
            ];

            foreach ($fixedSlabs as $slab) {
                DB::table('hr_commission_slabs')->insert([
                    'commission_setting_id' => $fixedId,
                    'profit_from'           => $slab['profit_from'],
                    'profit_to'             => $slab['profit_to'],
                    'value'                 => $slab['value'],
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }

            $this->command->info("Created: Sales Commission Plan (Slab Fixed) — ID {$fixedId} with 6 tiers.");
        } else {
            $this->command->warn('Skipped: Sales Commission Plan (Slab Fixed) already exists.');
        }
    }
}
