<?php

namespace App\Filament\Pages;

use App\Enums\RolesEnum;
use App\Models\Order;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $title = 'Site Settings';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::Admin->value) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'app_logo'    => Setting::get('app_logo', ''),
            'app_favicon' => Setting::get('app_favicon', ''),

            'website_commission'  => (float) Setting::get('website_commission', 0),
            'tax_rate'            => (float) Setting::get('tax_rate', 0),
            'prices_include_tax'  => (bool) (Setting::get('prices_include_tax', '0') === '1'),
            'currency'            => Setting::get('currency', 'USD'),
            'enabled_languages'   => json_decode(Setting::get('enabled_languages', '["en","ar"]'), true),
            'default_language'    => Setting::get('default_language', 'en'),

            // Hero
            'hero_badge'            => Setting::get('hero_badge', 'New Collection'),
            'hero_heading'          => Setting::get('hero_heading', 'Discover Your'),
            'hero_heading2'         => Setting::get('hero_heading2', 'Perfect Style'),
            'hero_subtext'          => Setting::get('hero_subtext', ''),
            'hero_cta_shop_label'   => Setting::get('hero_cta_shop_label', 'Shop Now'),
            'hero_cta_browse_label' => Setting::get('hero_cta_browse_label', 'Browse Departments'),
            'hero_bg_image_url'     => Setting::get('hero_bg_image_url', ''),

            // Blog banner
            'blog_banner_title'     => Setting::get('blog_banner_title', 'Stories, Tips & Style Guides'),
            'blog_banner_subtitle'  => Setting::get('blog_banner_subtitle', ''),
            'blog_banner_image_url' => Setting::get('blog_banner_image_url', ''),

            // Section visibility toggles
            'show_departments'       => Setting::get('show_departments', '1') === '1',
            'show_featured_products' => Setting::get('show_featured_products', '1') === '1',
            'show_best_sellers'      => Setting::get('show_best_sellers', '1') === '1',
            'show_recently_viewed'   => Setting::get('show_recently_viewed', '1') === '1',
            'show_blog_posts'        => Setting::get('show_blog_posts', '1') === '1',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Branding ─────────────────────────────────────────────────
                Forms\Components\Section::make('Branding')
                    ->description('Upload the site logo shown in the navbar and the favicon shown in the browser tab.')
                    ->schema([
                        Forms\Components\FileUpload::make('app_logo')
                            ->label('App Logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings/branding')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp', 'image/jpeg'])
                            ->helperText('Displayed in the navbar. Recommended height: 40 px. Formats: PNG, SVG, WebP.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('app_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('settings/branding')
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
                            ->helperText('Shown in browser tabs & bookmarks. Recommended 32×32 or 64×64 px. ICO, PNG or SVG.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Commission Settings')
                    ->description('Configure the platform commission deducted from each order before paying out the vendor.')
                    ->schema([
                        Forms\Components\TextInput::make('website_commission')
                            ->label('Website Commission (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->required()
                            ->helperText('e.g. 10 means 10% will be deducted from total_price. The remaining 90% is the vendor_subtotal.'),
                    ]),

                Forms\Components\Section::make('Tax Settings')
                    ->description('Configure the tax rate applied to orders. Choose whether product prices already include tax or not.')
                    ->schema([
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->default(0)
                            ->required()
                            ->helperText('e.g. 14 means 14% VAT. Set to 0 to disable tax.'),

                        Forms\Components\Toggle::make('prices_include_tax')
                            ->label('Prices include tax')
                            ->helperText('Enable if your product prices already include tax (tax-inclusive). Disable if tax should be added on top of the price (tax-exclusive).'),
                    ]),

                Forms\Components\Section::make('Currency')
                    ->description('Configure the store currency displayed on the frontend.')
                    ->schema([
                        Forms\Components\Select::make('currency')
                            ->label('Store Currency')
                            ->required()
                            ->searchable()
                            ->options([
                                'USD' => 'USD — US Dollar',
                                'EUR' => 'EUR — Euro',
                                'GBP' => 'GBP — British Pound',
                                'EGP' => 'EGP — Egyptian Pound',
                                'SAR' => 'SAR — Saudi Riyal',
                                'AED' => 'AED — UAE Dirham',
                                'QAR' => 'QAR — Qatari Riyal',
                                'KWD' => 'KWD — Kuwaiti Dinar',
                                'BHD' => 'BHD — Bahraini Dinar',
                                'JOD' => 'JOD — Jordanian Dinar',
                                'TRY' => 'TRY — Turkish Lira',
                                'CAD' => 'CAD — Canadian Dollar',
                                'AUD' => 'AUD — Australian Dollar',
                            ])
                            ->helperText('The ISO 4217 currency code used for all prices.'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Languages')
                    ->description('Choose which languages are available on the frontend and which is the default.')
                    ->schema([
                        Forms\Components\CheckboxList::make('enabled_languages')
                            ->label('Enabled Languages')
                            ->options([
                                'en' => 'English',
                                'ar' => 'Arabic',
                            ])
                            ->required()
                            ->minItems(1)
                            ->live()
                            ->helperText('At least one language must be enabled. Selecting only one makes it the default automatically.'),

                        Forms\Components\Select::make('default_language')
                            ->label('Default Language')
                            ->required()
                            ->options([
                                'en' => 'English',
                                'ar' => 'Arabic',
                            ])
                            ->hidden(fn (Get $get): bool => count((array) $get('enabled_languages')) < 2)
                            ->helperText('Applied to new visitors who have not yet set a preference.'),
                    ])
                    ->columns(2),

                // ── Homepage Hero ────────────────────────────────────────────
                Forms\Components\Section::make('Homepage Hero')
                    ->description('Customise the full-width hero banner shown at the top of the homepage.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('hero_badge')
                            ->label('Badge Text')
                            ->maxLength(60)
                            ->helperText('Small pill label above the headline (e.g. "New Collection").'),

                        Forms\Components\TextInput::make('hero_heading')
                            ->label('Headline Line 1')
                            ->maxLength(100)
                            ->helperText('First line of the main headline in white.'),

                        Forms\Components\TextInput::make('hero_heading2')
                            ->label('Headline Line 2 (gradient)')
                            ->maxLength(100)
                            ->helperText('Second line shown with a primary→secondary colour gradient.'),

                        Forms\Components\Textarea::make('hero_subtext')
                            ->label('Subtext')
                            ->rows(2)
                            ->maxLength(300)
                            ->helperText('Short paragraph below the headline.'),

                        Forms\Components\TextInput::make('hero_cta_shop_label')
                            ->label('Primary CTA Label')
                            ->maxLength(50)
                            ->helperText('Label for the "Shop Now" button.'),

                        Forms\Components\TextInput::make('hero_cta_browse_label')
                            ->label('Secondary CTA Label')
                            ->maxLength(50)
                            ->helperText('Label for the "Browse Departments" button.'),

                        Forms\Components\FileUpload::make('hero_bg_image_url')
                            ->label('Background Image')
                            ->image()
                            ->disk('public')
                            ->directory('settings/hero')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxSize(8192)
                            ->helperText('Optional. Recommended 1920×1080 px. Leave blank to use the default gradient.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // ── Blog Banner ──────────────────────────────────────────────
                Forms\Components\Section::make('Blog Banner')
                    ->description('Customise the hero banner shown at the top of the blog listing page.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('blog_banner_title')
                            ->label('Banner Title')
                            ->maxLength(120),

                        Forms\Components\Textarea::make('blog_banner_subtitle')
                            ->label('Banner Subtitle')
                            ->rows(2)
                            ->maxLength(300),

                        Forms\Components\FileUpload::make('blog_banner_image_url')
                            ->label('Background Image')
                            ->image()
                            ->disk('public')
                            ->directory('settings/blog')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxSize(8192)
                            ->helperText('Optional. Leave blank to use the default gradient background.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                // ── Homepage Section Visibility ──────────────────────────────
                Forms\Components\Section::make('Homepage Sections')
                    ->description('Toggle each homepage section on or off.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Toggle::make('show_departments')
                            ->label('Show Departments')
                            ->helperText('Show the departments grid on the homepage.'),

                        Forms\Components\Toggle::make('show_featured_products')
                            ->label('Show Featured Products')
                            ->helperText('Show the featured products section.'),

                        Forms\Components\Toggle::make('show_best_sellers')
                            ->label('Show Best Sellers')
                            ->helperText('Show the best-selling products section.'),

                        Forms\Components\Toggle::make('show_recently_viewed')
                            ->label('Show Recently Viewed')
                            ->helperText('Show the recently viewed products section (only visible to logged-in users).'),

                        Forms\Components\Toggle::make('show_blog_posts')
                            ->label('Show Latest Blog Posts')
                            ->helperText('Show the latest blog posts snippet on the homepage.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // ── Branding ─────────────────────────────────────────────────────────
        Setting::set('app_logo',    $this->resolveUploadedPath($data['app_logo'] ?? null));
        Setting::set('app_favicon', $this->resolveUploadedPath($data['app_favicon'] ?? null));

        // ── Commerce ────────────────────────────────────────────────────────
        Setting::set('website_commission', $data['website_commission']);
        Setting::set('tax_rate',            $data['tax_rate']);
        Setting::set('prices_include_tax',  $data['prices_include_tax'] ? '1' : '0');

        // ── Currency & Languages ─────────────────────────────────────────────
        Setting::set('currency', $data['currency']);
        $enabledLangs = array_values((array) $data['enabled_languages']);
        Setting::set('enabled_languages', json_encode($enabledLangs));
        $defaultLang = count($enabledLangs) === 1 ? $enabledLangs[0] : $data['default_language'];
        Setting::set('default_language', $defaultLang);

        // ── Hero ─────────────────────────────────────────────────────────────
        Setting::set('hero_badge',            $data['hero_badge'] ?? '');
        Setting::set('hero_heading',          $data['hero_heading'] ?? '');
        Setting::set('hero_heading2',         $data['hero_heading2'] ?? '');
        Setting::set('hero_subtext',          $data['hero_subtext'] ?? '');
        Setting::set('hero_cta_shop_label',   $data['hero_cta_shop_label'] ?? '');
        Setting::set('hero_cta_browse_label', $data['hero_cta_browse_label'] ?? '');
        Setting::set('hero_bg_image_url',     $this->resolveUploadedPath($data['hero_bg_image_url'] ?? null));

        // ── Blog Banner ──────────────────────────────────────────────────────
        Setting::set('blog_banner_title',     $data['blog_banner_title'] ?? '');
        Setting::set('blog_banner_subtitle',  $data['blog_banner_subtitle'] ?? '');
        Setting::set('blog_banner_image_url', $this->resolveUploadedPath($data['blog_banner_image_url'] ?? null));

        // ── Section Toggles ──────────────────────────────────────────────────
        Setting::set('show_departments',       $data['show_departments'] ? '1' : '0');
        Setting::set('show_featured_products', $data['show_featured_products'] ? '1' : '0');
        Setting::set('show_best_sellers',      $data['show_best_sellers'] ? '1' : '0');
        Setting::set('show_recently_viewed',   $data['show_recently_viewed'] ? '1' : '0');
        Setting::set('show_blog_posts',        $data['show_blog_posts'] ? '1' : '0');

        Cache::forget('site_settings');

        Notification::make()
            ->title('Settings saved.')
            ->success()
            ->send();
    }

    /** Normalise a FileUpload state value to a plain path string or empty. */
    private function resolveUploadedPath(mixed $value): string
    {
        if (is_array($value)) {
            $value = array_values(array_filter($value))[0] ?? null;
        }
        return $value ?? '';
    }

    public function recalculateOrders(): void
    {
        $rate = (float) Setting::get('website_commission', 0);

        $count = Order::all()->each(function (Order $order) use ($rate) {
            $commission = round($order->total_price * $rate / 100, 4);
            $order->update([
                'website_commission' => $commission,
                'vendor_subtotal'    => round($order->total_price - $commission, 4),
            ]);
        })->count();

        Notification::make()
            ->title("Recalculated commission for {$count} orders at {$rate}%.")
            ->success()
            ->send();
    }
}
