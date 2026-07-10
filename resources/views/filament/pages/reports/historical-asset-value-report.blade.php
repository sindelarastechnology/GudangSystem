<x-filament-panels::page>
    {{ $this->form }}

    @if($this->data['snapshot_date'] ?? null)
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
