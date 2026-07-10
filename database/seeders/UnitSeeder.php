<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Meter', 'symbol' => 'm'],
            ['name' => 'Roll', 'symbol' => 'rol'],
            ['name' => 'Pieces', 'symbol' => 'pcs'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Dus', 'symbol' => 'dus'],
            ['name' => 'Lembar', 'symbol' => 'lbr'],
            ['name' => 'Yard', 'symbol' => 'yd'],
            ['name' => 'Centimeter', 'symbol' => 'cm'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
