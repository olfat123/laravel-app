<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'currency'          => 'USD',
            'currency_locale'   => 'en-US',
            'enabled_languages' => '["en","ar"]',
            'default_language'  => 'en',
        ];

        foreach ($defaults as $key => $value) {
            if (! Setting::find($key)) {
                Setting::set($key, $value);
            }
        }
    }
}
