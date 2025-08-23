<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Dom\Text;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use App\Enums\ProductVariationTypeEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Model;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variations';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    public function form(Form $form): Form
    {
        $types = $this->record->variationTypes;
        $fields = [];
        foreach ($types as $type) {
            $fields[] = TextInput::make('variation_type_' . $type->id . '.id')
                ->hidden();
            $fields[] = Select::make('variation_type_' . $type->id . '.id')
                ->label($type->name)
                ->options($type->options->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->reactive();
        }
        return $form
            ->schema([
                Repeater::make('variations')
                    ->label(false)
                    ->collapsible()
                    ->defaultItems(1)
                    ->addActionLabel(__('Add new variation'))
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        Section::make()
                            ->label(__('Variation Options'))
                            ->columns(3)
                            ->schema($fields),
                        TextInput::make('quantity')
                            ->label(__('Quantity'))
                            ->numeric()
                            ->default(1),
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->default(0),
                    ])
                
            ]); 
    }   

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        $variationTypes = $this->record->variationTypes;
        $data['variations'] = $this->mergeCartesianWithExisting($variationTypes, $variations);
        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, array $existingData): array
    {
        $defaultQuantity = $this->record->quantity ?? 1;
        $defaultPrice = $this->record->price ?? 0;
        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);
        $mergedResult = [];
        foreach ($cartesianProduct as $product) {
            $optionIds = collect($product)
            ->filter(fn($value, $key) => str_starts_with($key, 'variation_type_'))
            ->map(fn($option) => $option['id'])
            ->values()
            ->toArray();
            
            $match = array_filter($existingData, function ($existingOption) use ($optionIds) {
                return $existingOption['variation_type_option_ids'] === $optionIds;
            });
            if (!empty($match)) {
                $existingEntry = reset($match);
                $product['id'] = $existingEntry['id'];
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            } else {
                $product['id'] = null;
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;
            }
            $mergedResult[] = $product;
        }
        
        return $mergedResult;
    }
    private function cartesianProduct($variationTypes, $defaultQuantity = null, $defaultPrice = null): array
    {
        $result = [[]];
        
        foreach ($variationTypes as $index => $variationType) {
            $temp = [];

            foreach ($variationType->options as $option) {
                foreach ($result as $combination) {
                    $newCombination = $combination + [
                        'variation_type_' . $variationType->id => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'label' => $variationType->name,
                        ],
                    ];
                    $temp[] = $newCombination;
                }
            }
            $result = $temp;
        }

        foreach ($result as &$combination) {
            if(count($combination) === count($variationTypes)) {
                $combination['quantity'] = $defaultQuantity;
                $combination['price']    = $defaultPrice;

            }
        }

        return $result;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $formattedVariations = [];
        foreach ($data['variations'] as $option) {
            $optionIds = [];
            foreach ($this->record->variationTypes as $key => $variationType) {
                if (isset($option['variation_type_' . $variationType->id]['id'])) {
                    $optionIds[] = $option['variation_type_' . $variationType->id]['id'];
                }
            }
            $quantity = $option['quantity'];
            $price = $option['price'];
            $formattedVariations[] = [
                'id' => $option['id'],
                'variation_type_option_ids' => json_encode($optionIds),
                'quantity' => $quantity,
                'price' => $price,
            ];
        }
        $data['variations'] = $formattedVariations;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variation = $data['variations'];
        unset($data['variations']);
        $record->variations()->delete();
        $record->variations()->upsert($variation, ['id'], ['variation_type_option_ids', 'quantity', 'price']);
        return $record;
    }
}
