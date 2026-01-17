<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DarbAssabilShipment extends Model
{
    use HasFactory;

    const STATUS_PENDING   = 'pending';
    const STATUS_CREATED   = 'created';
    const STATUS_FAILED    = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DELIVERED = 'delivered';

    protected $fillable = [
        'order_id',
        'darb_reference',
        'darb_id',
        'status',
        'amount',
        'currency',
        'recipient_name',
        'recipient_phone',
        'recipient_city',
        'recipient_area',
        'recipient_address',
        'api_request',
        'api_response',
        'error_message',
        'created_by',
        'synced_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'api_request'  => 'array',
        'api_response' => 'array',
        'synced_at'    => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if shipment can be synced.
     */
    public function canSync(): bool
    {
        return ! empty($this->darb_reference)
        && ! in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED]);
    }

    public function isSuccessful(): bool
    {
        // If we have a darb_id, the creation was successful even if status is pending
        if (! empty($this->darb_id)) {
            return ! in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED]);
        }

        return ! in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_PENDING]);
    }
}
