<?php
namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Concerns\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected static function booted()
    {
        static::created(function (Order $order) {
            if (empty($order->order_code)) {
                $order->order_code = 'O' . $order->id;
                $order->saveQuietly(); // بدون ما يشغل observers/loops
            }
        });
    }

    protected $fillable = [
        'order_code',
        'customer_id',
        'status',
        'payment_method',
        'notes',
        'region_id',
        'city_id',
    ];

    protected $casts = [
        'status'         => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->products->sum(function ($p) {
            $qty  = (int) ($p->pivot->quantity ?? 1);
            $unit = (float) ($p->pivot->unit_price ?? 0);
            return $qty * $unit;
        });
    }
}
