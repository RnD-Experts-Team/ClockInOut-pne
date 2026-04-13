<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder:
     * 1. Creates common global equipment records (store_id = null).
     * 2. Ensures a single global "Others" catch-all record exists.
     * 3. Auto-matches existing maintenance_requests by comparing
     *    equipment_with_issue (case-insensitive) to equipment.name.
     * 4. Any unmatched MRs are assigned to the "Others" record.
     */
    public function run(): void
    {
        // ── Step 1: Seed common equipment (global, no store) ─────────────────
        $globalEquipment = [
            ['name' => 'Walk-in Cooler',     'type' => 'Refrigeration'],
            ['name' => 'Walk-in Freezer',    'type' => 'Refrigeration'],
            ['name' => 'Reach-in Cooler',    'type' => 'Refrigeration'],
            ['name' => 'Reach-in Freezer',   'type' => 'Refrigeration'],
            ['name' => 'Ice Machine',         'type' => 'Refrigeration'],
            ['name' => 'HVAC Unit',           'type' => 'HVAC'],
            ['name' => 'Exhaust Fan',         'type' => 'HVAC'],
            ['name' => 'Grease Trap',         'type' => 'Plumbing'],
            ['name' => 'Drain Line',          'type' => 'Plumbing'],
            ['name' => 'Hot Water Heater',    'type' => 'Plumbing'],
            ['name' => 'Electrical Panel',    'type' => 'Electrical'],
            ['name' => 'Lighting Fixture',    'type' => 'Electrical'],
            ['name' => 'Fryer',               'type' => 'Kitchen Equipment'],
            ['name' => 'Oven',                'type' => 'Kitchen Equipment'],
            ['name' => 'Grill',               'type' => 'Kitchen Equipment'],
            ['name' => 'Hood System',         'type' => 'Kitchen Equipment'],
            ['name' => 'POS System',          'type' => 'Technology'],
            ['name' => 'Security Camera',     'type' => 'Technology'],
            ['name' => 'Drive-Through System','type' => 'Technology'],
            ['name' => 'Ice Cream Machine',   'type' => 'Kitchen Equipment'],
            ['name' => 'Shake Machine',       'type' => 'Kitchen Equipment'],
            ['name' => 'Coffee Machine',      'type' => 'Kitchen Equipment'],
            ['name' => 'Soda Fountain',       'type' => 'Kitchen Equipment'],
        ];

        foreach ($globalEquipment as $item) {
            Equipment::firstOrCreate(
                ['name' => $item['name'], 'store_id' => null],
                array_merge($item, ['is_active' => true])
            );
        }

        // ── Step 2: Ensure "Others" catch-all record exists ──────────────────
        $others = Equipment::firstOrCreate(
            ['name' => 'Others', 'store_id' => null],
            ['type' => null, 'is_active' => true]
        );

        // ── Step 3: Build a name→id lookup map (lowercase) ──────────────────
        $equipmentMap = Equipment::all()
            ->keyBy(fn ($e) => strtolower(trim($e->name)))
            ->mapWithKeys(fn ($e, $key) => [$key => $e->id]);

        // ── Step 4: Auto-match existing MRs ─────────────────────────────────
        $this->command->info('Auto-matching existing maintenance requests to equipment...');

        $matched   = 0;
        $unmatched = 0;

        // Process in chunks to avoid memory issues on large datasets
        MaintenanceRequest::whereNull('equipment_id')
            ->whereNotNull('equipment_with_issue')
            ->chunkById(200, function ($requests) use ($equipmentMap, $others, &$matched, &$unmatched) {
                foreach ($requests as $mr) {
                    $key = strtolower(trim((string) $mr->equipment_with_issue));

                    if ($key && isset($equipmentMap[$key])) {
                        DB::table('maintenance_requests')
                            ->where('id', $mr->id)
                            ->update(['equipment_id' => $equipmentMap[$key]]);
                        $matched++;
                    } else {
                        DB::table('maintenance_requests')
                            ->where('id', $mr->id)
                            ->update(['equipment_id' => $others->id]);
                        $unmatched++;
                    }
                }
            });

        // ── Step 5: Assign "Others" to any MRs still null (no equipment_with_issue) ──
        $noText = DB::table('maintenance_requests')
            ->whereNull('equipment_id')
            ->update(['equipment_id' => $others->id]);

        $this->command->info("Done. Matched: {$matched} | Assigned to Others: " . ($unmatched + $noText));
    }
}
