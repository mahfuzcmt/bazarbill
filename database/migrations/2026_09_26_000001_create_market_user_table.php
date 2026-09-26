<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which markets a user may work in. users.market_id stays the market
        // the user is currently working in (the "active" market).
        Schema::create('market_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['market_id', 'user_id']);
        });

        // Every existing user keeps access to the market they already belong to.
        $now = now();
        DB::table('users')->whereNotNull('market_id')->orderBy('id')->chunk(500, function ($users) use ($now) {
            DB::table('market_user')->insert($users->map(fn ($u) => [
                'market_id' => $u->market_id, 'user_id' => $u->id, 'created_at' => $now, 'updated_at' => $now,
            ])->all());
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_user');
    }
};
