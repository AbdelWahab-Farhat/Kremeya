<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'disk',
        'alt',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path || ! $this->disk) {
            return null;
        }
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk ?? 'public');

        return $disk->url($this->path);
    }
}
