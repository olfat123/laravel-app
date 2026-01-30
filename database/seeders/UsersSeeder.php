<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
        Vendor::factory()->create([
            'user_id' => $vendor->id,
            'status' => VendorStatusEnum::APPROVED,
            'store_name' => 'Vendor Store',
            'store_address' => '123 Vendor St, City, Country',
        ]); 

        $customer = User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
        ]);

        $customer->assignRole(RolesEnum::Customer->value);
    }
}
