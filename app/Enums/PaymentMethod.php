<?php
namespace App\Enums;

enum PaymentMethod: string {
    case CASH   = 'cash';
    case WALLET = 'wallet';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH   => 'نقدي',
            self::WALLET => 'محفظة',
        };
    }
}
