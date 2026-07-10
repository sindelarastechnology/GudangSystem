<?php

namespace App\Filament\Pages\Reports;

use App\Models\AssetValueSnapshot;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class HistoricalAssetValueReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static string $view = 'filament.pages.reports.historical-asset-value-report';

    protected static ?string $navigationLabel = 'Nilai Aset Historis';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 9;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Laporan Nilai Aset Historis';
    }

    public function mount(): void
    {
        $this->form->fill([
            'snapshot_date' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('snapshot_date')
                    ->label('Pilih Bulan')
                    ->default(now()->subMonth()->endOfMonth())
                    ->required()
                    ->live(),
                Forms\Components\Select::make('warehouse_id')
                    ->label('Gudang')
                    ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->live(),
            ]);
    }

    public function table(Table $table): Table
    {
        $snapshotDate = $this->data['snapshot_date'] ?? null;
        $warehouseId = $this->data['warehouse_id'] ?? null;

        return $table
            ->query(
                AssetValueSnapshot::query()
                    ->when($snapshotDate, fn ($q) => $q->whereDate('snapshot_date', $snapshotDate))
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->with(['warehouse', 'rawMaterial.unit'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rawMaterial.code')
                    ->label('Kode Item')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rawMaterial.name')
                    ->label('Nama Item')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) =>
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    ),
                Tables\Columns\TextColumn::make('avg_cost')
                    ->label('Harga Rata-rata')
                    ->money('IDR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('asset_value')
                    ->label('Nilai Aset')
                    ->money('IDR')
                    ->alignRight()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total'),
                    ]),
                Tables\Columns\TextColumn::make('snapshot_date')
                    ->label('Tanggal Snapshot')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('warehouse.name')
            ->paginated([25, 50, 100]);
    }
}
