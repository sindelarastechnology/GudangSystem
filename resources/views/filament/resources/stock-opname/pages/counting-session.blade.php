<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Session Info Card --}}
        <x-filament::section>
            <x-slot name="heading">
                Informasi Sesi Opname
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">No. Opname</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $opname->opname_number }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Gudang</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $warehouse->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $opname->opname_date->format('d M Y') }}
                    </p>
                </div>
            </div>

            <x-filament::section.description class="mt-4">
                <div class="flex items-start gap-2">
                    <x-filament::icon
                        icon="heroicon-o-information-circle"
                        class="w-5 h-5 text-primary-600 dark:text-primary-400"
                    />
                    <div class="text-sm">
                        <p class="font-medium mb-1">Petunjuk Input Fisik:</p>
                        <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                            <li>Hitung jumlah fisik barang di gudang</li>
                            <li>Input jumlah fisik pada kolom yang tersedia</li>
                            <li>Sistem akan otomatis menghitung selisih</li>
                            <li>Klik <strong>Finalisasi Opname</strong> jika sudah selesai menghitung semua item</li>
                        </ul>
                    </div>
                </div>
            </x-filament::section.description>
        </x-filament::section>

        {{-- Items Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Daftar Item di Gudang
            </x-slot>

            <x-slot name="description">
                Input jumlah fisik untuk setiap item. Qty Sistem adalah jumlah terakhir di database.
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Kode</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Nama Item</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">Qty Sistem</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Satuan</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">Qty Fisik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-mono text-xs">{{ $item['raw_material_code'] }}</td>
                                <td class="px-4 py-3">{{ $item['raw_material_name'] }}</td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ number_format($item['system_qty'], 2) }}
                                </td>
                                <td class="px-4 py-3">{{ $item['unit_symbol'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            value="{{ $item['physical_qty'] }}"
                                            wire:model.blur="physicalQty.{{ $item['raw_material_id'] }}"
                                            wire:change="saveDraft({{ $item['raw_material_id'] }}, $event.target.value)"
                                            class="w-32 text-right border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                        @php
                                            $currentPhysical = $this->physicalQty[$item['raw_material_id']] ?? $item['physical_qty'];
                                            $diff = $currentPhysical - $item['system_qty'];
                                        @endphp
                                        @if($diff != 0)
                                            <span class="text-xs font-medium px-2 py-1 rounded {{ $diff > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada item di gudang ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Summary Card --}}
        @php
            $totalItems = count($items);
            $itemsCounted = collect($items)->filter(fn($item) => $item['physical_qty'] > 0)->count();
        @endphp

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Progress Penghitungan</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $itemsCounted }} / {{ $totalItems }} item
                    </p>
                </div>

                <div class="flex gap-2">
                    <x-filament::badge color="warning">
                        Status: Menghitung
                    </x-filament::badge>
                </div>
            </div>

            @if($itemsCounted < $totalItems)
                <x-filament::section.description class="mt-4">
                    <div class="flex items-start gap-2 text-amber-600 dark:text-amber-400">
                        <x-filament::icon
                            icon="heroicon-o-exclamation-triangle"
                            class="w-5 h-5"
                        />
                        <span class="text-sm">
                            Masih ada {{ $totalItems - $itemsCounted }} item yang belum dihitung. 
                            Pastikan semua item sudah diinput sebelum finalisasi.
                        </span>
                    </div>
                </x-filament::section.description>
            @else
                <x-filament::section.description class="mt-4">
                    <div class="flex items-start gap-2 text-green-600 dark:text-green-400">
                        <x-filament::icon
                            icon="heroicon-o-check-circle"
                            class="w-5 h-5"
                        />
                        <span class="text-sm font-medium">
                            Semua item sudah dihitung. Anda dapat finalisasi opname sekarang.
                        </span>
                    </div>
                </x-filament::section.description>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
