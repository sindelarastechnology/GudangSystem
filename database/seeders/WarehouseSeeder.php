<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Gudang Pusat',
                'code' => 'GDG-PST',
                'location' => 'Jl. Industri No. 1, Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Gudang Cabang Blitar',
                'code' => 'GDG-BLR',
                'location' => 'Jl. Raya Blitar No. 25, Blitar',
                'is_active' => true,
            ],
            [
                'name' => 'Gudang Cabang Bandung',
                'code' => 'GDG-BDG',
                'location' => 'Jl. Cilaki No. 50, Bandung',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $w) {
            Warehouse::create($w);
        }
    }
}
