<?php

namespace App\Filament\Widgets;

use App\Models\MaterialStock;
use App\Models\RawMaterial;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CriticalStockListWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Stok Kritis - Perlu Perhatian Segera';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaterialStock::query()
                    ->whereRaw('current_stock <= min_stock')
                    ->where('min_stock', '>', 0)
                    ->with(['rawMaterial.unit', 'rawMaterial.materialCategory', 'warehouse'])
                    ->orderByRaw('(current_stock / NULLIF(min_stock, 0)) ASC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('severity')
                    ->label('')
                    ->badge()
                    ->state(function (MaterialStock $record): string {
                        $percentage = ($record->current_stock / $record->min_stock) * 100;
                        if ($percentage <= 0) return 'HABIS';
                        if ($percentage <= 25) return 'KRITIS';
                        if ($percentage <= 50) return 'RENDAH';
                        if ($percentage <= 75) return 'SEDANG';
                        return 'HAMPIR';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'HABIS' => 'danger',
                        'KRITIS' => 'danger',
                        'RENDAH' => 'warning',
                        'SEDANG' => 'info',
                        'HAMPIR' => 'gray',
                    })
                    ->sortable(false)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rawMaterial.code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kode disalin')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rawMaterial.name')
                    ->label('Nama Item')
                    ->searchable()
                    ->wrap()
                    ->weight('medium')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rawMaterial.materialCategory.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_info')
                    ->label('Stok')
                    ->state(function (MaterialStock $record): string {
                        return number_format($record->current_stock, 1) . ' / ' . 
                               number_format($record->min_stock, 1) . ' ' . 
                               $record->rawMaterial->unit->symbol;
                    })
                    ->description(function (MaterialStock $record): string {
                        $percentage = $record->min_stock > 0 
                            ? ($record->current_stock / $record->min_stock) * 100 
                            : 0;
                        return number_format($percentage, 0) . '% dari minimum';
                    })
                    ->color(function (MaterialStock $record): string {
                        $percentage = $record->min_stock > 0 
                            ? ($record->current_stock / $record->min_stock) * 100 
                            : 0;
                        if ($percentage <= 25) return 'danger';
                        if ($percentage <= 50) return 'warning';
                        return 'gray';
                    })
                    ->alignRight()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('shortage')
                    ->label('Kurang')
                    ->state(function (MaterialStock $record): string {
                        $shortage = $record->min_stock - $record->current_stock;
                        return number_format($shortage, 1) . ' ' . $record->rawMaterial->unit->symbol;
                    })
                    ->color('danger')
                    ->alignRight()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('current_avg_cost')
                    ->label('Harga/Unit')
                    ->money('IDR')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_cost')
                    ->label('Est. Nilai Restock')
                    ->state(function (MaterialStock $record): float {
                        $shortage = max(0, $record->min_stock - $record->current_stock);
                        return $shortage * $record->current_avg_cost;
                    })
                    ->money('IDR')
                    ->description('Perkiraan biaya untuk mencapai min stock')
                    ->alignRight()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('last_notified_at')
                    ->label('Notif Terakhir')
                    ->dateTime('d M Y H:i')
                    ->description(function ($state) {
                        if (!$state) return 'Belum pernah';
                        return $state->diffForHumans();
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->relationship('warehouse', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('severity')
                    ->label('Tingkat Keparahan')
                    ->options([
                        'habis' => 'Habis (0%)',
                        'kritis' => 'Kritis (≤25%)',
                        'rendah' => 'Rendah (≤50%)',
                        'sedang' => 'Sedang (≤75%)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['value']),
                            function (Builder $query) use ($data): Builder {
                                return match ($data['value']) {
                                    'habis' => $query->where('current_stock', '<=', 0),
                                    'kritis' => $query->whereRaw('(current_stock / NULLIF(min_stock, 0)) <= 0.25'),
                                    'rendah' => $query->whereRaw('(current_stock / NULLIF(min_stock, 0)) <= 0.50'),
                                    'sedang' => $query->whereRaw('(current_stock / NULLIF(min_stock, 0)) <= 0.75'),
                                    default => $query,
                                };
                            }
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_stock_card')
                    ->label('Kartu Stok')
                    ->icon('heroicon-m-document-text')
                    ->color('info')
                    ->url(fn (MaterialStock $record): string => 
                        route('filament.admin.resources.raw-materials.view', [
                            'record' => $record->raw_material_id,
                        ])
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('create_stock_in')
                    ->label('Buat Pembelian')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->url(fn (): string => 
                        route('filament.admin.resources.stock-in-transactions.create')
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Tidak ada stok kritis')
            ->emptyStateDescription('Semua item memiliki stok di atas minimum. Sistem berjalan dengan baik!')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultPaginationPageOption(10)
            ->poll('30s');
    }
}
