<?php

namespace App\Enums;

enum UserRoles : string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';

    case Employee = 'employee';

    
        public static function values() {
        return array_map(fn(self $c) => $c->value , self::cases());
    }
}
