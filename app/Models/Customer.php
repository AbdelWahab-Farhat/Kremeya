<?php
namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, HasActivityLogs;

    protected $fillable = [
        'user_id',
        'city_id',
        'region_id',
        'gender',
    ];

    protected $casts = [
        'gender'    => Gender::class, // يرجّع Gender enum بدل string
        'is_active' => 'boolean',
    ];

    // (اختياري) إخفاء حقول معينة من اللوج
    public array $activityLogHiddenFields = ['user_id'];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = 'TMP-' . (string) Str::ulid(); // unique

            }
        });

        static::created(function (Customer $customer) {
            $customer->customer_code = 'C' . $customer->id;
            $customer->saveQuietly();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

}
