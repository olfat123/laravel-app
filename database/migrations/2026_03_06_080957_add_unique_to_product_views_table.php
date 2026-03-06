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
        Schema::table('product_views', function (Blueprint $table) {
            // Drop plain indexes first, replace with unique
            $table->dropIndex(['product_id', 'user_id']);
            $table->unique(['product_id', 'user_id'], 'product_views_product_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_views', function (Blueprint $table) {
            $table->dropUnique('product_views_product_user_unique');
            $table->index(['product_id', 'user_id']);
        });
    }
};
