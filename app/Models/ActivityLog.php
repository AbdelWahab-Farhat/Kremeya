<?php
namespace App\Models;

use App\Enums\Action;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'message',
        'user_id',
        'data',
    ];

    protected $casts = [
        'data'   => 'array',
        'action' => Action::class,
    ];

    public function loggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
