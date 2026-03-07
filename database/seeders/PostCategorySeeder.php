<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {

        $categories = [
            ['name' => 'Style Tips',        'name_ar' => 'نصائح الأناقة',   'slug' => 'style-tips'],
            ['name' => 'Trends',            'name_ar' => 'اتجاهات الموضة',  'slug' => 'trends'],
            ['name' => 'Lookbooks',         'name_ar' => 'دفاتر الإطلالات', 'slug' => 'lookbooks'],
            ['name' => 'Outfit Ideas',      'name_ar' => 'أفكار للتنسيق',   'slug' => 'outfit-ideas'],
            ['name' => 'Seasonal Guides',   'name_ar' => 'أدلة الموسم',     'slug' => 'seasonal-guides'],
            ['name' => 'Shopping Guides',   'name_ar' => 'أدلة التسوق',     'slug' => 'shopping-guides'],
            ['name' => "Kids' Fashion",     'name_ar' => 'أزياء الأطفال',   'slug' => 'kids-fashion'],
            ['name' => 'Care & Maintenance','name_ar' => 'العناية والصيانة', 'slug' => 'care-maintenance'],
        ];

        foreach ($categories as $cat) {
            PostCategory::create($cat);
        }
    }
}
