<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            // subscription: credits included in the plan
            // recharge:     offline top-up (bKash / Nagad / cash) entered by super admin
            // adjustment:   manual correction, may be positive or negative
            // usage:        consumed by an outgoing SMS
            // refund:       returned when a gateway send fails
            $table->string('type', 20);
            $table->integer('amount'); // signed: + adds credits, - removes credits
            $table->unsignedInteger('balance_after');
            $table->string('reference')->nullable(); // e.g. bKash TrxID, invoice no
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sms_log_id')->nullable()->constrained('sms_logs')->nullOnDelete();
            $table->timestamps();

            $table->index(['market_id', 'created_at']);
            $table->index(['market_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_credit_transactions');
    }
};
