<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 2000);
            $table->string('slug', 2000);
            $table->longText('description')->nullable();
            $table->decimal('price', 20, 4);
            $table->decimal('discount_price', 20, 4)->nullable();
            $table->foreignId('department_id')->index()->constrained('departments');
            $table->string('sku', 200)->nullable();
            $table->string('status')->index();
            $table->integer('quantity')->default(0);
            $table->foreignId('category_id')->index()->constrained('categories');
            $table->foreignIdFor(User::class, 'created_by');
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
