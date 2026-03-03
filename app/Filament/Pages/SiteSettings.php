<?php

namespace App\Filament\Pages;

use App\Enums\RolesEnum;
use App\Models\Order;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

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
            'website_commission' => (float) Setting::get('website_commission', 0),
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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('website_commission', $data['website_commission']);

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
