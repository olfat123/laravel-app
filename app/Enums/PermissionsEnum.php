<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case ViewDashboard = 'view dashboard';
    case ViewSettings = 'view settings';
    case UpdateSettings = 'update settings';

    case ViewRoles = 'view roles';
    case CreateRoles = 'create roles';
    case UpdateRoles = 'update roles';
    case DeleteRoles = 'delete roles';

    case ViewPermissions = 'view permissions';
    case CreatePermissions = 'create permissions';
    case UpdatePermissions = 'update permissions';
    case DeletePermissions = 'delete permissions';

    case ApproveVendor = 'approve vendor';
    case RejectVendor = 'reject vendor';

    case ViewUsers = 'view users';
    case CreateUsers = 'create users';
    case UpdateUsers = 'update users';
    case DeleteUsers = 'delete users';


    case ViewProducts = 'view products';
    case CreateProducts = 'create products';
    case UpdateProducts = 'update products';
    case DeleteProducts = 'delete products';

    case ViewOrders = 'view orders';
    case CreateOrders = 'create orders';
    case UpdateOrders = 'update orders';
    case DeleteOrders = 'delete orders';
}
