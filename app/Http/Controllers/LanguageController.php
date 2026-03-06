<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $siteSettings   = Cache::get('site_settings');
        $availableLocales = $siteSettings['available_locales']
            ?? json_decode(Setting::get('enabled_languages', '["en","ar"]'), true)
            ?: ['en', 'ar'];

        abort_unless(in_array($locale, $availableLocales), 404);

        session(['locale' => $locale]);
        App::setLocale($locale);

        return redirect()->back();
    }
}
