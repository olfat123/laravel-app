<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Enums\VendorStatusEnum;
use App\Filament\Resources\VendorResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVendor extends ViewRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool =>
                    $this->record->vendor && $this->record->vendor->status !== VendorStatusEnum::APPROVED->value
                )
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->vendor->update(['status' => VendorStatusEnum::APPROVED->value]);
                }),

            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool =>
                    $this->record->vendor && $this->record->vendor->status !== VendorStatusEnum::REJECTED->value
                )
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->vendor->update(['status' => VendorStatusEnum::REJECTED->value]);
                }),
        ];
    }
}
