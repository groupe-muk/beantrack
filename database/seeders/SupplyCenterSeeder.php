<?php

namespace Database\Seeders;

use App\Models\SupplyCenter;
use Illuminate\Database\Seeder;

class SupplyCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        SupplyCenter::factory(5)->create();
    }
}
