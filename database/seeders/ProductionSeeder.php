<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed data master minimum untuk production.
     * 
     * Hanya seed data esensial yang dibutuhkan sistem untuk operasional awal.
     * Data lain (raw_materials, suppliers, dll) diinput manual via Filament.
     */
    public function run(): void
    {
        $this->seedMaterialCategories();
        $this->seedUnits();
        $this->seedWarehouses();
        
        $this->command->info('✅ Production seeder completed successfully!');
    }

    private function seedMaterialCategories(): void
    {
        $categories = [
            ['code' => 'UMUM', 'name' => 'Bahan Umum'],
        ];

        foreach ($categories as $category) {
            MaterialCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        $this->command->info('  → Material categories seeded');
    }

    private function seedUnits(): void
    {
        $units = [
            ['name' => 'Pieces', 'symbol' => 'pcs'],
            ['name' => 'Meter', 'symbol' => 'm'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['symbol' => $unit['symbol']],
                $unit
            );
        }

        $this->command->info('  → Units seeded');
    }

    private function seedWarehouses(): void
    {
        $warehouses = [
            [
                'code' => 'GD-PUSAT',
                'name' => 'Gudang Pusat',
                'location' => null,
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::firstOrCreate(
                ['code' => $warehouse['code']],
                $warehouse
            );
        }

        $this->command->info('  → Warehouses seeded');
    }
}
