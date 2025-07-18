<?php

namespace App\Enums;

enum RolesEnum: string
{
    case Admin = 'admin';
    case Vendor = 'vendor';
    case Customer = 'customer';
}
