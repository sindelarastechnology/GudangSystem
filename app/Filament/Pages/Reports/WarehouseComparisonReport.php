<?php

namespace App\Filament\Pages\Reports;

use App\Models\MaterialStock;
use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseComparisonReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static string $view = 'filament.pages.reports.warehouse-comparison-report';

    protected static ?string $navigationLabel = 'Perbandingan Antar Gudang';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 8;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Perbandingan Stok Item Antar Gudang';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('raw_material_id')
                    ->label('Pilih Item')
                    ->options(RawMaterial::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->live(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaterialStock::query()
                    ->when(
                        $this->data['raw_material_id'] ?? null,
                        fn (Builder $query, $itemId) => $query->where('raw_material_id', $itemId)
                    )
                    ->with(['warehouse', 'rawMaterial.unit'])
                    ->whereHas('rawMaterial', fn ($q) => $q->where('is_active', true))
                    ->orderBy('current_asset_value', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    ),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min Stok')
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
            ->paginated([25, 50, 100]);
    }

    public function hasData(): bool
    {
        return !empty($this->data['raw_material_id']);
    }
}
