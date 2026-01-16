<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

    protected $fillable = [
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_items')->withPivot(['quantity'])->withTimestamps();
    }

    public function getTotalQuantityAttribute(): int
    {
        if ($this->relationLoaded('products')) {
            return (int) $this->products->sum(fn ($p) => (int) ($p->pivot->quantity ?? 0));
        }

        return (int) $this->products()->sum('cart_items.quantity');
    }
     // عدد المنتجات المختلفة في السلة
    public function getProductsCountAttribute(): int
    {
        if ($this->relationLoaded('products')) {
            return (int) $this->products->count();
        }

        // fallback query
        return (int) $this->products()->count();
    }

     // إجمالي السعر = selling_price * quantity
    public function getTotalPriceAttribute(): float
    {
        if ($this->relationLoaded('products')) {
            $total = $this->products->sum(function ($p) {
                $price = (float) ($p->selling_price ?? 0);
                $qty   = (int) ($p->pivot->quantity ?? 0);
                return $price * $qty;
            });

            return (float) $total;
        }
        return (float) ($total ?? 0);
    }


}
