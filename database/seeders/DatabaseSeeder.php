<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Runs a clean re-seed: wipes relevant tables, then re-populates everything.
     */
    public function run(): void
    {
        // Wipe tables that are not handled inside their own seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        DB::table('vendors')->truncate();
        DB::table('orders')->truncate();
        DB::table('order_items')->truncate();
        DB::table('cart_items')->truncate();
        DB::table('product_reviews')->truncate();
        DB::table('product_views')->truncate();
        DB::table('wishlists')->truncate();
        DB::table('user_addresses')->truncate();
        DB::table('post_categories')->truncate();
        DB::table('posts')->truncate();
        DB::table('coupons')->truncate();
        // products + media deleted via cascade from product seeder, handled below
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call([
            RolesSeeder::class,          // Roles & permissions
            UsersSeeder::class,          // Admin / vendor / customer
            DepartmentSeeder::class,     // Women / Men / Kids  (also truncates categories)
            CategorySeeder::class,       // Clothing categories per department
            PostCategorySeeder::class,   // Blog post categories
            CouponSeeder::class,         // Sample coupons
            DefaultSettingsSeeder::class,// All site settings incl. hero + toggles
        ]);
    }
}

