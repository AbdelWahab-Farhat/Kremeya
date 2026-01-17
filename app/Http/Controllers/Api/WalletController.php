<?php
namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletTransactionRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\Customer;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * عرض محفظة زبون معين
     */
    public function customerWallet(Customer $customer)
    {
        $wallet = $this->walletService->getOrCreateWallet($customer->id);
        $wallet->load('customer.user');

        return response()->json([
            'success' => true,
            'data'    => new WalletResource($wallet),
        ]);
    }

    /**
     * عرض جميع المحافظ في النظام
     */
    public function index(Request $request)
    {
        $wallets = Wallet::with(['customer.user'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('customer.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return WalletResource::collection($wallets);
    }

    /**
     * عرض جميع المعاملات المالية
     */
    public function transactions(Request $request)
    {
        $transactions = WalletTransaction::with(['wallet.customer.user'])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->wallet_id, function ($query, $walletId) {
                $query->where('wallet_id', $walletId);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return WalletTransactionResource::collection($transactions);
    }

    /**
     * معاملات محفظة زبون معين
     */
    public function customerTransactions(Customer $customer)
    {
        $wallet = $customer->wallet;

        if (! $wallet) {
            return response()->json([
                'success' => false,
                'message' => 'الزبون لا يملك محفظة',
            ], 404);
        }

        $transactions = $wallet->transactions()
            ->latest()
            ->paginate(15);

        return WalletTransactionResource::collection($transactions);
    }

    /**
     * إضافة أو سحب من المحفظة (للأدمن فقط)
     */
    public function transact(WalletTransactionRequest $request, Customer $customer)
    {
        $wallet      = $this->walletService->getOrCreateWallet($customer->id);
        $type        = TransactionType::from($request->type);
        $amount      = $request->amount;
        $description = $request->description;

        try {
            if ($type === TransactionType::CREDIT) {
                $transaction = $this->walletService->deposit($wallet, $amount, $description);
            } else {
                $transaction = $this->walletService->withdraw($wallet, $amount, $description);
            }

            return response()->json([
                'success' => true,
                'message' => $type === TransactionType::CREDIT ? 'تم الإيداع بنجاح' : 'تم السحب بنجاح',
                'data'    => [
                    'transaction' => new WalletTransactionResource($transaction),
                    'wallet'      => new WalletResource($wallet->fresh()),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
