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
        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('payment_option', ['full', 'half'])->default('full')->after('total_amount');
            $table->decimal('downpayment_amount', 10, 2)->nullable()->after('payment_option');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['payment_option', 'downpayment_amount']);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid')->change();
        });
    }
};
