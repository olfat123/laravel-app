<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Clear child tables first to respect FK constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Category::truncate();
        Department::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $departments = [
            [
                'name'             => "Women's Fashion",
                'slug'             => 'womens-fashion',
                'meta_title'       => "Women's Fashion – Dresses, Tops, Shoes & More",
                'meta_description' => "Shop the latest women's clothing, shoes, and accessories.",
                'active'           => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => "Men's Fashion",
                'slug'             => 'mens-fashion',
                'meta_title'       => "Men's Fashion – Shirts, Pants, Shoes & More",
                'meta_description' => "Discover modern men's clothing, shoes, and accessories.",
                'active'           => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => "Kids' Fashion",
                'slug'             => 'kids-fashion',
                'meta_title'       => "Kids' Fashion – Clothes & Shoes for Boys & Girls",
                'meta_description' => 'Find stylish and comfortable clothing for kids of all ages.',
                'active'           => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        Department::insert($departments);
    }
}
