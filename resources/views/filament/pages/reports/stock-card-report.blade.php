<x-filament-panels::page>
    {{ $this->form }}

    @if($this->hasData())
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
