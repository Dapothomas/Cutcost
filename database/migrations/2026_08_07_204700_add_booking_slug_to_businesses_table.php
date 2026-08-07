<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->boolean('public_booking_enabled')->default(true)->after('address');
        });

        foreach (DB::table('businesses')->orderBy('id')->get() as $business) {
            $base = Str::slug($business->name) ?: 'shop';
            $slug = $base;
            $i = 2;

            while (
                DB::table('businesses')
                    ->where('slug', $slug)
                    ->where('id', '!=', $business->id)
                    ->exists()
            ) {
                $slug = $base.'-'.$i;
                $i++;
            }

            DB::table('businesses')->where('id', $business->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['slug', 'public_booking_enabled']);
        });
    }
};
