<?php

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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('shop_number');
            $table->string('floor')->nullable();
            $table->decimal('area_sqft', 10, 2)->nullable();
            $table->decimal('rent_amount', 12, 2);
            $table->decimal('advance_deposit', 12, 2)->default(0);
            $table->enum('shop_type', ['general', 'food', 'clothing', 'electronics', 'jewelry', 'pharmacy', 'other'])->default('general');
            $table->enum('status', ['active', 'vacant', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['market_id', 'shop_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
