<?php

namespace App\Filament\Pages\Reports;

use App\Models\MaterialStock;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CurrentAssetValueReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string $view = 'filament.pages.reports.current-asset-value-report';

    protected static ?string $navigationLabel = 'Nilai Aset Saat Ini';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    public function getTitle(): string
    {
        return 'Laporan Nilai Aset Saat Ini';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaterialStock::query()
                    ->where('current_stock', '>', 0)
                    ->with(['rawMaterial.unit', 'warehouse'])
                    ->orderBy('warehouse_id')
                    ->orderBy('raw_material_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rawMaterial.code')
                    ->label('Kode Item')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('rawMaterial.name')
                    ->label('Nama Item')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Qty')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    ),
                Tables\Columns\TextColumn::make('current_avg_cost')
                    ->label('Harga Rata-rata')
                    ->money('IDR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('current_asset_value')
                    ->label('Nilai Aset')
                    ->money('IDR')
                    ->alignRight()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total'),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                    ->multiple(),
            ])
            ->defaultSort('warehouse.name')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\TotalAssetValueWidget::class,
        ];
    }
}
