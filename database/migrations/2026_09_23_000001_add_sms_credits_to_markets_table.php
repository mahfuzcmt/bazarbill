<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            // Prepaid SMS balance sold by the platform (super admin) to the market.
            $table->unsignedInteger('sms_credits')->default(0)->after('sms_templates');
        });

        Schema::table('sms_logs', function (Blueprint $table) {
            // 'platform' = sent through BazarBill's gateway and billed in credits,
            // 'own' = sent through the market's own API key (no credits consumed).
            $table->string('gateway', 20)->default('platform')->after('status');
            $table->unsignedSmallInteger('credits_used')->default(0)->after('gateway');
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'credits_used']);
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn('sms_credits');
        });
    }
};
