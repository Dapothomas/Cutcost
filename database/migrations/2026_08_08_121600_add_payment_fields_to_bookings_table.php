<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->unsignedInteger('amount_cents')->nullable()->after('payment_status');
            $table->string('stripe_checkout_session_id')->nullable()->after('amount_cents');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'amount_cents', 'stripe_checkout_session_id']);
        });
    }
};
