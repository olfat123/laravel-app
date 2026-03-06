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
            'website_commission'  => (float) Setting::get('website_commission', 0),
            'tax_rate'            => (float) Setting::get('tax_rate', 0),
            'prices_include_tax'  => (bool) (Setting::get('prices_include_tax', '0') === '1'),
            'currency'          => Setting::get('currency', 'USD'),
            'enabled_languages' => json_decode(Setting::get('enabled_languages', '["en","ar"]'), true),
            'default_language'  => Setting::get('default_language', 'en'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('website_commission', $data['website_commission']);
        Setting::set('tax_rate',            $data['tax_rate']);
        Setting::set('prices_include_tax',  $data['prices_include_tax'] ? '1' : '0');
        Setting::set('currency',          $data['currency']);
        $enabledLangs = array_values((array) $data['enabled_languages']);
        Setting::set('enabled_languages', json_encode($enabledLangs));
        // If only one language is enabled it becomes the default automatically.
        $defaultLang = count($enabledLangs) === 1 ? $enabledLangs[0] : $data['default_language'];
        Setting::set('default_language',  $defaultLang);

        Cache::forget('site_settings');

        Notification::make()
            ->title('Settings saved.')
            ->success()
            ->send();
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
