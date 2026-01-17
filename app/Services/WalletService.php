<?php
namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * إيداع مبلغ في المحفظة
     */
    public function deposit(Wallet $wallet, float $amount, ?string $description = null, ?Model $transactionable = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new Exception('المبلغ يجب أن يكون أكبر من صفر', 400);
        }

        return DB::transaction(function () use ($wallet, $amount, $description, $transactionable) {
            $balanceBefore = $wallet->balance;
            $wallet->increment('balance', $amount);

            return $wallet->transactions()->create([
                'type'                 => TransactionType::CREDIT,
                'amount'               => $amount,
                'balance_before'       => $balanceBefore,
                'balance_after'        => $wallet->fresh()->balance,
                'description'          => $description,
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id'   => $transactionable?->id,
            ]);
        });
    }

    /**
     * سحب مبلغ من المحفظة
     */
    public function withdraw(Wallet $wallet, float $amount, ?string $description = null, ?Model $transactionable = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new Exception('المبلغ يجب أن يكون أكبر من صفر', 400);
        }

        if (! $wallet->hasEnoughBalance($amount)) {
            throw new Exception('رصيد غير كافي في المحفظة', 400);
        }

        return DB::transaction(function () use ($wallet, $amount, $description, $transactionable) {
            $balanceBefore = $wallet->balance;
            $wallet->decrement('balance', $amount);

            return $wallet->transactions()->create([
                'type'                 => TransactionType::DEBIT,
                'amount'               => $amount,
                'balance_before'       => $balanceBefore,
                'balance_after'        => $wallet->fresh()->balance,
                'description'          => $description,
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id'   => $transactionable?->id,
            ]);
        });
    }

    /**
     * الحصول على محفظة الزبون أو إنشاء واحدة جديدة
     */
    public function getOrCreateWallet(int $customerId): Wallet
    {
        return Wallet::firstOrCreate(
            ['customer_id' => $customerId],
            ['balance' => 0]
        );
    }
}
