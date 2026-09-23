<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            // trial | active | expired | cancelled
            $table->string('status', 20);
            $table->string('billing_cycle', 10)->default('monthly'); // monthly | yearly | trial
            $table->date('starts_at');
            $table->date('ends_at');
            // When the next monthly SMS allocation is due (yearly plans get credits month by month).
            $table->date('next_sms_allocation_at')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_method', 20)->nullable(); // bkash | nagad | rocket | bank | cash
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['market_id', 'status']);
            $table->index(['status', 'ends_at']);
        });

        Schema::table('markets', function (Blueprint $table) {
            // Denormalised copy of the current subscription for cheap checks on every request.
            $table->foreignId('plan_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->string('subscription_status', 20)->nullable()->after('plan_id');
            $table->date('subscription_ends_at')->nullable()->after('subscription_status');
        });
    }

    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['subscription_status', 'subscription_ends_at']);
        });

        Schema::dropIfExists('subscriptions');
    }
};
