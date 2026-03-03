<?php

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Filament\Resources\VendorResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Vendors';

    protected static ?string $modelLabel = 'Vendor';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role(RolesEnum::Vendor->value)
            ->with('vendor');
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()->hasRole(RolesEnum::Admin);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Account')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime()
                            ->placeholder('Not verified'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Registered At')
                            ->dateTime(),
                    ]),

                Infolists\Components\Section::make('Store Profile')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('vendor.store_name')
                            ->label('Store Name')
                            ->placeholder('No profile yet'),
                        Infolists\Components\TextEntry::make('vendor.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                VendorStatusEnum::APPROVED->value => 'success',
                                VendorStatusEnum::PENDING->value  => 'warning',
                                VendorStatusEnum::REJECTED->value => 'danger',
                                default                           => 'gray',
                            })
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vendor.store_address')
                            ->label('Store Address')
                            ->placeholder('—'),
                    ]),

                Infolists\Components\Section::make('Bank Account Details')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('vendor.bank_name')
                            ->label('Bank Name')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vendor.bank_account_name')
                            ->label('Account Name')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vendor.bank_account_number')
                            ->label('Account Number')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vendor.bank_swift_code')
                            ->label('SWIFT Code')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('vendor.bank_iban')
                            ->label('IBAN')
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor.store_name')
                    ->label('Store Name')
                    ->searchable()
                    ->placeholder('No store yet'),
                Tables\Columns\TextColumn::make('vendor.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        VendorStatusEnum::APPROVED->value => 'success',
                        VendorStatusEnum::PENDING->value  => 'warning',
                        VendorStatusEnum::REJECTED->value => 'danger',
                        default                           => 'gray',
                    })
                    ->placeholder('No profile'),
                Tables\Columns\TextColumn::make('vendor.store_address')
                    ->label('Address')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vendor_status')
                    ->label('Store Status')
                    ->options([
                        VendorStatusEnum::PENDING->value  => 'Pending',
                        VendorStatusEnum::APPROVED->value => 'Approved',
                        VendorStatusEnum::REJECTED->value => 'Rejected',
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $data['value']
                            ? $query->whereHas('vendor', fn ($q) => $q->where('status', $data['value']))
                            : $query
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool =>
                        $record->vendor && $record->vendor->status !== VendorStatusEnum::APPROVED->value
                    )
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->vendor->update(['status' => VendorStatusEnum::APPROVED->value])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool =>
                        $record->vendor && $record->vendor->status !== VendorStatusEnum::REJECTED->value
                    )
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->vendor->update(['status' => VendorStatusEnum::REJECTED->value])),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'view'  => Pages\ViewVendor::route('/{record}'),
        ];
    }
}
