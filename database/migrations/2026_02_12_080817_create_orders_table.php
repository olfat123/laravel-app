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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class, 'user_id');
            $table->foreignIdFor(\App\Models\User::class, 'vendor_user_id');
            $table->decimal('total_price', 20, 4);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->decimal('online_payment_commission', 20, 4)->nullable();
            $table->decimal('website_commission', 20, 4)->nullable();
            $table->decimal('vendor_subtotal', 20, 4)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_intent')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Order::class, 'order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Product::class, 'product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('price', 20, 4);
            $table->json('variation_type_option_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
