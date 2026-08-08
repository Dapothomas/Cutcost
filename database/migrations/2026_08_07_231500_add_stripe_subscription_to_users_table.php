<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_plan')->nullable()->after('business_id');
            $table->string('subscription_status')->default('pending')->after('subscription_plan');
            $table->string('stripe_customer_id')->nullable()->after('subscription_status');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'subscription_status',
                'stripe_customer_id',
                'stripe_subscription_id',
            ]);
        });
    }
};
