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
            'g', 'kg', 'oz', 'lbs', 'ml', 'L',
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit]);
        }
    }
}
