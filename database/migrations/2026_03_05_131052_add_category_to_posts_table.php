<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'post_category_id')) {
                $table->foreignId('post_category_id')
                    ->nullable()
                    ->after('author_id')
                    ->constrained('post_categories')
                    ->nullOnDelete();
            } else {
                // Column exists but FK may be missing — add the constraint
                $table->foreign('post_category_id')
                    ->references('id')->on('post_categories')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\PostCategory::class);
            $table->dropColumn('post_category_id');
        });
    }
};
