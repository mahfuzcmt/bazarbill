<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('last_reminder_at')->nullable()->after('generated_at');
            $table->unsignedSmallInteger('reminder_count')->default(0)->after('last_reminder_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['last_reminder_at', 'reminder_count']);
        });
    }
};
