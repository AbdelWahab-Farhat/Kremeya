<?php

namespace App\Enums;

enum OrderStatus : string
{
    case NEW  = 'new';
    case ON_DELIVERY  = 'on_delivery';
    case CANCELLED  = 'cancelled';
    case PAID  = 'paid';

    case SETTLEMENT = 'settlement';

    public static function values() {
        return array_map(fn(self $c) => $c->value , self::cases());
    }
}
