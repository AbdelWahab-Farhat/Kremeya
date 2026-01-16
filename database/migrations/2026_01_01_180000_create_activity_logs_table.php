<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->string('action');             // create / update / delete / ...
            $table->text('message')->nullable();  // وصف

            // بدل model + model_id
            $table->morphs('loggable');           // loggable_id + loggable_type + index

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('data')->nullable();     // before/after, metadata...

            $table->timestamps();

            $table->index('action');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
