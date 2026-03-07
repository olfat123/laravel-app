<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // DepartmentSeeder already truncated categories; look up IDs dynamically.
        $women = Department::where('slug', 'womens-fashion')->value('id');
        $men   = Department::where('slug', 'mens-fashion')->value('id');
        $kids  = Department::where('slug', 'kids-fashion')->value('id');

        // ── Women's Fashion ──────────────────────────────────────────────
        $womenCategories = [
            'Dresses',
            'Tops & Blouses',
            'Skirts',
            'Pants & Jeans',
            'Jackets & Coats',
            'Abayas & Modest Wear',
            'Activewear',
            'Swimwear',
            'Lingerie & Sleepwear',
            'Shoes',
            'Bags & Handbags',
            'Belts & Accessories',
        ];

        foreach ($womenCategories as $name) {
            Category::create([
                'name'          => $name,
                'department_id' => $women,
                'parent_id'     => null,
                'active'        => true,
            ]);
        }

        // ── Men's Fashion ────────────────────────────────────────────────
        $menCategories = [
            'T-Shirts & Polos',
            'Casual Shirts',
            'Formal Shirts',
            'Pants & Chinos',
            'Jeans',
            'Suits & Blazers',
            'Jackets & Coats',
            'Activewear',
            'Shoes',
            'Caps & Hats',
            'Belts & Accessories',
            'Underwear & Socks',
        ];

        foreach ($menCategories as $name) {
            Category::create([
                'name'          => $name,
                'department_id' => $men,
                'parent_id'     => null,
                'active'        => true,
            ]);
        }

        // ── Kids' Fashion ────────────────────────────────────────────────
        $kidsCategories = [
            "Girls' Clothing",
            "Boys' Clothing",
            'Baby & Toddler',
            'Kids\' Shoes',
            'School Uniforms',
            'Kids\' Activewear',
            'Kids\' Accessories',
            'Swimwear & Beachwear',
        ];

        foreach ($kidsCategories as $name) {
            Category::create([
                'name'          => $name,
                'department_id' => $kids,
                'parent_id'     => null,
                'active'        => true,
            ]);
        }
    }
}
