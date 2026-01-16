<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use App\Observers\ActivityLoggerObserver;

trait HasActivityLogs
{
    public static function bootHasActivityLogs(): void
    {
        static::observe(ActivityLoggerObserver::class);
    }

    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }
}
