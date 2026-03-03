<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Forms\Components\Select::make('status')
                        ->options(OrderStatusEnum::labels())
                        ->required()
                        ->default(fn () => $this->record->status),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->default(fn () => $this->record->notes),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                        'notes'  => $data['notes'] ?? $this->record->notes,
                    ]);
                    $this->refreshFormData(['status', 'notes']);
                }),
        ];
    }
}
