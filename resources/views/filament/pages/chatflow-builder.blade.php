<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-between">
            <x-filament::button
                type="button"
                color="gray"
                tag="a"
                :href="$this->getResource()::getUrl('edit', ['record' => $record])"
            >
                Back to Edit
            </x-filament::button>

            <div class="flex gap-3">
                {{ $this->validateAction }}
                {{ $this->saveAction }}
            </div>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
