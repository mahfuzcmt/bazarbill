<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->nullable(); // null = yearly not offered
            $table->unsignedInteger('shop_limit')->nullable();   // null = unlimited
            $table->unsignedInteger('sms_credits_per_month')->default(0);
            $table->unsignedInteger('trial_days')->default(14);
            $table->unsignedInteger('trial_sms_credits')->default(20);
            $table->json('features')->nullable(); // e.g. {"masking_sms": true}
            $table->boolean('is_default')->default(false); // plan used for self-service signups
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
