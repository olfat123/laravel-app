<?php

namespace App\Filament\Pages;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class VendorProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Store Profile';

    protected static ?string $title = 'My Store Profile';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.vendor-profile';

    // Form state
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->hasRole(RolesEnum::Vendor) ?? false;
    }

    public function mount(): void
    {
        $vendor = Vendor::firstOrNew(['user_id' => Auth::id()]);

        $this->form->fill([
            'store_name'          => $vendor->store_name,
            'store_address'       => $vendor->store_address,
            'bank_name'           => $vendor->bank_name,
            'bank_account_name'   => $vendor->bank_account_name,
            'bank_account_number' => $vendor->bank_account_number,
            'bank_swift_code'     => $vendor->bank_swift_code,
            'bank_iban'           => $vendor->bank_iban,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Store Details')
                    ->description('Public information shown to customers.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->label('Store Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('store_address')
                            ->label('Store Address')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Bank Account Details')
                    ->description('Used for payouts. This information is private.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('bank_swift_code')
                            ->label('SWIFT / BIC Code')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('bank_iban')
                            ->label('IBAN')
                            ->maxLength(50)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $existing = Vendor::where('user_id', Auth::id())->first();

        Vendor::updateOrCreate(
            ['user_id' => Auth::id()],
            array_merge($data, [
                // Only set to pending on first creation; preserve existing status otherwise
                'status' => $existing?->status ?? VendorStatusEnum::PENDING->value,
            ])
        );

        Notification::make()
            ->title('Profile saved successfully!')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
