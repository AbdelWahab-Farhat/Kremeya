<?php
namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class ActivityLoggerObserver
{
    /**
     * حقول ممنوع تتسجل في اللوج (افتراضي عام)
     */
    protected array $globalHidden = [
        'password',
        'remember_token',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'otp',
        'pin',
    ];

    /**
     * حقول "Noise" ما نبيها تتسجل غالباً
     */
    protected array $globalIgnore = [
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    public function created(Model $model): void
    {
        $this->log($model, 'create', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $after = $model->getChanges();
        if ($this->isOnlyIgnoredChange($after, $model)) {
            return;
        }

        $original = $model->getOriginal();

        $before = [];
        foreach ($after as $key => $newValue) {
            $before[$key] = $original[$key] ?? null;
        }

        $this->log($model, 'update', $before, $after);
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'delete', $model->getOriginal(), null);
    }

    protected function log(Model $model, string $action, $before, $after): void
    {
        // منع loop
        if ($model instanceof \App\Models\ActivityLog) {
            return;
        }

        // تعطيل اختياري
        if (property_exists($model, 'disableActivityLog') && $model->disableActivityLog) {
            return;
        }

        // لازم يكون عنده logs()
        if (! method_exists($model, 'logs')) {
            return;
        }

        // فلترة قبل/بعد
        $filteredBefore = is_array($before) ? $this->filterPayload($model, $before) : null;
        $filteredAfter  = is_array($after) ? $this->filterPayload($model, $after) : null;

        $model->logs()->create([
            'action'  => $action,
            'message' => $this->message($model, $action, $filteredAfter),
            'user_id' => auth()->id(),
            'data'    => [
                'before' => $filteredBefore,
                'after'  => $filteredAfter,
            ],
        ]);
    }

    protected function message(Model $model, string $action, ?array $after): string
    {
        $name = class_basename($model);

        // مثال رسالة أوضح في update
        if ($action === 'update' && is_array($after) && count($after) > 0) {
            $fields = implode(', ', array_keys($after));
            return "{$name} updated: {$fields}";
        }

        return "{$name} {$action}";
    }

    /**
     * فلترة payload: تستثني hidden + ignore + hiddenFields من الموديل
     */
    protected function filterPayload(Model $model, array $payload): array
    {
        $hidden = array_unique(array_merge(
            $this->globalHidden,
            $model->getHidden(),                 // hidden في Laravel model
            $model->activityLogHiddenFields ?? []// تخصيص للموديل (اختياري)
        ));

        $ignore = array_unique(array_merge(
            $this->globalIgnore,
            $model->activityLogIgnoreFields ?? []// تخصيص للموديل (اختياري)
        ));

        // شيل hidden
        foreach ($hidden as $key) {
            unset($payload[$key]);
        }

        // شيل ignore
        foreach ($ignore as $key) {
            unset($payload[$key]);
        }

        return $payload;
    }

    /**
     * لو التغييرات كلها ضمن ignore (مثل updated_at فقط) => تجاهل التسجيل
     */
    protected function isOnlyIgnoredChange(array $changes, Model $model): bool
    {
        $ignore = array_unique(array_merge(
            $this->globalIgnore,
            $model->activityLogIgnoreFields ?? []
        ));

        $keys = array_keys($changes);
        if (count($keys) === 0) {
            return true;
        }

        foreach ($keys as $k) {
            if (! in_array($k, $ignore, true)) {
                return false;
            }

        }
        return true;
    }
}
