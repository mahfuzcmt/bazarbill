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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('market_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('name_bn')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'market_owner', 'collector', 'shop_owner'])->default('shop_owner')->after('phone');
            $table->string('avatar')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->enum('language_preference', ['en', 'bn'])->default('bn')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropColumn(['market_id', 'name_bn', 'phone', 'role', 'avatar', 'is_active', 'language_preference']);
        });
    }
};
