<?php

namespace App\Enums;

enum Action  : string
{
    case CREATE   = 'create';
    case UPDATE = 'update';
    case DELETE  = 'delete';
    case READ  = 'read';

    public static function values() {
        return array_map(fn(self $c) => $c->value , self::cases());
    }
}
