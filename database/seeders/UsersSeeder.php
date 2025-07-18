<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\RolesEnum;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $admin->assignRole(RolesEnum::Admin->value);

        $vendor = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@example.com',
        ]);

        $vendor->assignRole(RolesEnum::Vendor->value);  

        $customer = User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
        ]);

        $customer->assignRole(RolesEnum::Customer->value);
    }
}
