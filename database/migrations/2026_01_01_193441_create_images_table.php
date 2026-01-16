<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            // Polymorphic: imageable_type + imageable_id
            $table->morphs('imageable'); // creates imageable_id, imageable_type + index

            // Where the file is stored (path or url)
            $table->string('path'); // e.g. "products/123/img1.jpg"

            // Optional metadata
            $table->string('disk')->default('public'); // or "s3"
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['imageable_type', 'imageable_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
