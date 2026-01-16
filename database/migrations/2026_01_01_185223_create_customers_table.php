<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();

            // At Creating  Adding Code In Boot.
            $table->string("customer_code")->unique();
            $table->foreignId('city_id')->nullable();
            $table->foreignId('region_id')->nullable();
            $table->enum('gender', Gender::values())->default(Gender::OTHER->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
