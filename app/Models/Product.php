<?php
namespace App\Models;

use App\Models\Concerns\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected $fillable = [
        'name',
        'description',
        'selling_price',
        'buying_price',
        'is_active',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'buying_price'  => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = self::generateUniqueSku();
            }
        });
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot(['quantity', 'unit_price'])
            ->withTimestamps();
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_items')->withPivot('quantity')->withTimestamps();
    }

    private static function generateUniqueSku(): string
    {
        do {
            $sku = 'SKU-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (self::withTrashed()->where('sku', $sku)->exists()); // مع soft deletes

        return $sku;
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('sort_order');
    }

    public function primaryImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')->where('is_primary', true);
    }
}
