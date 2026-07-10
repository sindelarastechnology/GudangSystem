<?php

namespace App\Filament\Pages\Reports;

use App\Models\MaterialStock;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CriticalStockReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string $view = 'filament.pages.reports.critical-stock-report';

    protected static ?string $navigationLabel = 'Stok Kritis';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 6;

    public function getTitle(): string
    {
        return 'Laporan Stok Kritis';
    }

    public function getHeading(): string
    {
        return 'Daftar Item dengan Stok di Bawah Minimum';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaterialStock::query()
                    ->whereRaw('current_stock <= min_stock')
                    ->where('min_stock', '>', 0)
                    ->with(['rawMaterial.unit', 'rawMaterial.materialCategory', 'warehouse'])
                    ->orderBy('warehouse_id')
                    ->orderByRaw('(current_stock / NULLIF(min_stock, 0))')
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
                Tables\Columns\TextColumn::make('rawMaterial.materialCategory.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    )
                    ->color('danger'),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    ),
                Tables\Columns\TextColumn::make('shortage')
                    ->label('Kekurangan')
                    ->alignRight()
                    ->getStateUsing(fn ($record) => max(0, $record->min_stock - $record->current_stock))
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    )
                    ->color('warning'),
                Tables\Columns\TextColumn::make('percentage')
                    ->label('% dari Min')
                    ->alignRight()
                    ->getStateUsing(fn ($record) => 
                        $record->min_stock > 0 
                            ? ($record->current_stock / $record->min_stock) * 100 
                            : 0
                    )
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->color(fn ($state) => match (true) {
                        $state < 50 => 'danger',
                        $state < 100 => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                    ->multiple(),
            ])
            ->paginated([25, 50, 100])
            ->poll('60s');
    }
}
