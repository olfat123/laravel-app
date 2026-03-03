<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-4">
            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>

            <x-filament::button type="button" color="gray" wire:click="recalculateOrders"
                wire:confirm="This will overwrite website_commission and vendor_subtotal on ALL existing orders using the currently saved rate. Continue?">
                Recalculate All Orders
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
