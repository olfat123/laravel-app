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
        Schema::create('variation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('variation_type_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('variation_type_id')
                ->index()
                ->constrained('variation_types')
                ->onDelete('cascade');
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->index()
                ->constrained('products')
                ->onDelete('cascade');
            $table->json('variation_type_option_ids');
            $table->decimal('price', 20, 4)->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('variation_type_options');
        Schema::dropIfExists('variation_types');
    }
};
