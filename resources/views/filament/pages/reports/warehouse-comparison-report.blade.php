<x-filament-panels::page>
    {{ $this->form }}
    
    @if($this->hasData())
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @else
        <div class="mt-6">
            <x-filament::section>
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <x-filament::icon 
                        icon="heroicon-o-scale" 
                        class="w-16 h-16 text-gray-400 mb-4"
                    />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        Pilih Item untuk Membandingkan
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md">
                        Laporan ini menampilkan perbandingan stok, harga rata-rata, dan nilai aset untuk item yang sama di berbagai gudang. Pilih item di atas untuk melihat perbandingan.
                    </p>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
