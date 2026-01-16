<?php

namespace App\Enums;

enum Gender: string
{
    case MALE   = 'male';
    case FEMALE = 'female';
    case OTHER  = 'other';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}
