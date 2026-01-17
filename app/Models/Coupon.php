<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'type',
        'value',
        'expiry_date',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'value'       => 'float',
        'usage_limit' => 'integer',
        'used_count'  => 'integer',
        'is_active'   => 'boolean',
    ];

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
