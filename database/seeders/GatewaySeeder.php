<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gateway::insert([
            ['id' => 1, 'name' => 'gateway_1', 'is_active' => true, 'priority' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'gateway_2', 'is_active' => false, 'priority' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
