<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Aktivitas Terbaru
        </x-slot>

        <x-slot name="description">
            20 transaksi terakhir dari semua gudang
        </x-slot>

        <div class="space-y-2">
            @forelse($this->getActivities() as $activity)
                @php
                    $colorClasses = [
                        'success' => [
                            'bg' => 'bg-green-100 dark:bg-green-900/30',
                            'text' => 'text-green-600 dark:text-green-400',
                            'badge' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                        ],
                        'danger' => [
                            'bg' => 'bg-red-100 dark:bg-red-900/30',
                            'text' => 'text-red-600 dark:text-red-400',
                            'badge' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                        ],
                        'info' => [
                            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
                            'text' => 'text-blue-600 dark:text-blue-400',
                            'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                        ],
                        'warning' => [
                            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
                            'text' => 'text-yellow-600 dark:text-yellow-400',
                            'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                        ],
                    ];
                    $colors = $colorClasses[$activity['color']] ?? $colorClasses['success'];
                @endphp

                <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $colors['bg'] }}">
                            <x-filament::icon :icon="$activity['icon']" class="w-5 h-5 {{ $colors['text'] }}" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colors['badge'] }}">
                                        {{ $activity['type_label'] }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $activity['number'] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mb-1">
                                    <x-filament::icon icon="heroicon-m-building-storefront" class="w-4 h-4 text-gray-400" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $activity['warehouse'] }}</span>
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $activity['details'] }}</p>

                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-m-user" class="w-3 h-3" />
                                        <span>{{ $activity['user'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-m-clock" class="w-3 h-3" />
                                        <span>{{ $activity['time'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $activity['date'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <x-filament::icon icon="heroicon-o-document-text" class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-3" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada aktivitas</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Transaksi akan muncul di sini setelah dibuat</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
