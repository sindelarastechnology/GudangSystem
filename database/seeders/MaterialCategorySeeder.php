<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kain', 'code' => 'KIN'],
            ['name' => 'Benang', 'code' => 'BNG'],
            ['name' => 'Kancing', 'code' => 'KNC'],
            ['name' => 'Resleting', 'code' => 'RSL'],
            ['name' => 'Karet', 'code' => 'KRT'],
            ['name' => 'Label', 'code' => 'LBL'],
            ['name' => 'Kemasan', 'code' => 'KMS'],
            ['name' => 'Bahan Pelapis', 'code' => 'LPS'],
        ];

        foreach ($categories as $cat) {
            MaterialCategory::create($cat);
        }
    }
}
