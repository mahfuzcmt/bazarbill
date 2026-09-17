<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow mobile banking / bank transfer methods and store their transaction reference.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cash')->change();
            $table->string('transaction_reference', 100)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_reference');
            $table->enum('payment_method', ['cash'])->default('cash')->change();
        });
    }
};
