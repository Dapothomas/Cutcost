<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bachs_customer_id')->nullable()->after('stripe_subscription_id');
            $table->string('bachs_subscription_id')->nullable()->after('bachs_customer_id');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('bachs_account_id')->nullable()->after('stripe_onboarding_completed_at');
            $table->boolean('bachs_charges_enabled')->default(false)->after('bachs_account_id');
            $table->boolean('bachs_payouts_enabled')->default(false)->after('bachs_charges_enabled');
            $table->timestamp('bachs_onboarding_completed_at')->nullable()->after('bachs_payouts_enabled');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('bachs_checkout_id')->nullable()->after('stripe_checkout_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bachs_customer_id', 'bachs_subscription_id']);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'bachs_account_id',
                'bachs_charges_enabled',
                'bachs_payouts_enabled',
                'bachs_onboarding_completed_at',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('bachs_checkout_id');
        });
    }
};
