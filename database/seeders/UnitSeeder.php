<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Pcs',
                'code' => 'PCS',
                'description' => 'Piece / Unit',
            ],
            [
                'name' => 'Box',
                'code' => 'BOX',
                'description' => 'Box / Kotak',
            ],
            [
                'name' => 'Kg',
                'code' => 'KG',
                'description' => 'Kilogram',
            ],
            [
                'name' => 'Liter',
                'code' => 'L',
                'description' => 'Liter',
            ],
            [
                'name' => 'Meter',
                'code' => 'M',
                'description' => 'Meter',
            ],
            [
                'name' => 'Roll',
                'code' => 'ROLL',
                'description' => 'Roll / Gulung',
            ],
            [
                'name' => 'Set',
                'code' => 'SET',
                'description' => 'Set / Paket',
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
