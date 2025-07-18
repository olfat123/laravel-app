<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Empty roles and permissions tables before seeding
        app('cache')->forget('spatie.permission.cache');
        Role::query()->delete();
        Permission::query()->delete();
        $adminRole = Role::create(['name' => RolesEnum::Admin->value]);
        $vendorRole = Role::create(['name' => RolesEnum::Vendor->value]);
        $customerRole = Role::create(['name' => RolesEnum::Customer->value]);

        $approveVendorPermission = Permission::create(['name' => PermissionsEnum::ApproveVendor->value]);
        $rejectVendorPermission = Permission::create(['name' => PermissionsEnum::RejectVendor->value]);
        $viewDashboardPermission = Permission::create(['name' => PermissionsEnum::ViewDashboard->value]);
        $viewSettingsPermission = Permission::create(['name' => PermissionsEnum::ViewSettings->value]);
        $updateSettingsPermission = Permission::create(['name' => PermissionsEnum::UpdateSettings->value]);

        $viewUsersPermission = Permission::create(['name' => PermissionsEnum::ViewUsers->value]);
        $createUsersPermission = Permission::create(['name' => PermissionsEnum::CreateUsers->value]);
        $updateUsersPermission = Permission::create(['name' => PermissionsEnum::UpdateUsers->value]);
        $deleteUsersPermission = Permission::create(['name' => PermissionsEnum::DeleteUsers->value]);

        $viewProductsPermission = Permission::create(['name' => PermissionsEnum::ViewProducts->value]);
        $createProductsPermission = Permission::create(['name' => PermissionsEnum::CreateProducts->value]);
        $updateProductsPermission = Permission::create(['name' => PermissionsEnum::UpdateProducts->value]);
        $deleteProductsPermission = Permission::create(['name' => PermissionsEnum::DeleteProducts->value]);

        $viewOrdersPermission = Permission::create(['name' => PermissionsEnum::ViewOrders->value]);
        $createOrdersPermission = Permission::create(['name' => PermissionsEnum::CreateOrders->value]);
        $updateOrdersPermission = Permission::create(['name' => PermissionsEnum::UpdateOrders->value]);
        $deleteOrdersPermission = Permission::create(['name' => PermissionsEnum::DeleteOrders->value]);

        $adminRole->syncPermissions([
            PermissionsEnum::ViewDashboard->value,
            PermissionsEnum::ViewSettings->value,
            PermissionsEnum::UpdateSettings->value,
            PermissionsEnum::ViewUsers->value,
            PermissionsEnum::CreateUsers->value,
            PermissionsEnum::UpdateUsers->value,
            PermissionsEnum::DeleteUsers->value,
            PermissionsEnum::ViewProducts->value,
            PermissionsEnum::CreateProducts->value,
            PermissionsEnum::UpdateProducts->value,
            PermissionsEnum::DeleteProducts->value,
            PermissionsEnum::ViewOrders->value,
            PermissionsEnum::CreateOrders->value,
            PermissionsEnum::UpdateOrders->value,
            PermissionsEnum::DeleteOrders->value,
        ]);

        $vendorRole->syncPermissions([
            PermissionsEnum::ViewProducts->value,
            PermissionsEnum::CreateProducts->value,
            PermissionsEnum::UpdateProducts->value,
            PermissionsEnum::DeleteProducts->value,
            PermissionsEnum::ViewOrders->value,
            PermissionsEnum::CreateOrders->value,
            PermissionsEnum::UpdateOrders->value,
        ]);

        $customerRole->syncPermissions([
            PermissionsEnum::ViewProducts->value,
            PermissionsEnum::ViewOrders->value,
        ]);

    }
}
