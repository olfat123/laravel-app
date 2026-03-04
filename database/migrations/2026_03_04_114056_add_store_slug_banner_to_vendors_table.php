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
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('store_slug')->nullable()->unique()->after('store_name');
            $table->string('store_description')->nullable()->after('store_address');
        });

        // Back-fill slugs for existing vendors
        foreach (\DB::table('vendors')->get() as $vendor) {
            $slug = \Illuminate\Support\Str::slug($vendor->store_name);
            $base  = $slug;
            $i     = 1;
            while (\DB::table('vendors')->where('store_slug', $slug)->where('user_id', '!=', $vendor->user_id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            \DB::table('vendors')->where('user_id', $vendor->user_id)->update(['store_slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['store_slug', 'store_description']);
        });
    }
};
