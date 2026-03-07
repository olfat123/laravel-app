<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── General ─────────────────────────────────────────────────────
            'currency'          => 'USD',
            'currency_locale'   => 'en-US',
            'enabled_languages' => '["en","ar"]',
            'default_language'  => 'en',

            // ── Commerce ─────────────────────────────────────────────────────
            'website_commission'  => '0',
            'tax_rate'            => '0',
            'prices_include_tax'  => '0',

            // ── Homepage Hero ────────────────────────────────────────────────
            'hero_badge'           => 'New Collection',
            'hero_heading'         => 'Discover Your',
            'hero_heading2'        => 'Perfect Style',
            'hero_subtext'         => 'Shop the latest trends in Women\'s, Men\'s and Kids\' fashion — free shipping on orders over $50.',
            'hero_cta_shop_label'  => 'Shop Now',
            'hero_cta_browse_label'=> 'Browse Departments',
            'hero_bg_image_url'    => '',

            // ── Blog Banner ──────────────────────────────────────────────────
            'blog_banner_title'     => 'Stories, Tips & Style Guides',
            'blog_banner_subtitle'  => 'Explore our latest articles on fashion trends, styling tips and seasonal lookbooks.',
            'blog_banner_image_url' => '',

            // ── Homepage Section Visibility ──────────────────────────────────
            'show_departments'       => '1',
            'show_featured_products' => '1',
            'show_best_sellers'      => '1',
            'show_recently_viewed'   => '1',
            'show_blog_posts'        => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
