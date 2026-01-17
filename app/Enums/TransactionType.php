<?php
namespace App\Enums;

enum TransactionType: string {
    case CREDIT = 'credit'; // إيداع
    case DEBIT  = 'debit';  // سحب

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'إيداع',
            self::DEBIT  => 'سحب',
        };
    }
}
