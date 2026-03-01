<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'code' => 'ELEC',
                'description' => 'Barang elektronik dan komponen',
            ],
            [
                'name' => 'Perkakas',
                'code' => 'TOOL',
                'description' => 'Perkakas dan alat kerja',
            ],
            [
                'name' => 'Material',
                'code' => 'MAT',
                'description' => 'Material dan bahan baku',
            ],
            [
                'name' => 'Spare Part',
                'code' => 'SP',
                'description' => 'Spare part dan suku cadang',
            ],
            [
                'name' => 'Konsumsi',
                'code' => 'CONS',
                'description' => 'Barang konsumsi dan habis pakai',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
