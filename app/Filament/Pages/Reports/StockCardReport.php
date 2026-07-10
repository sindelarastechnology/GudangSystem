<?php

namespace App\Filament\Pages\Reports;

use App\Models\RawMaterial;
use App\Models\StockLedger;
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

class StockCardReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.stock-card-report';

    protected static ?string $navigationLabel = 'Kartu Stok';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Laporan Kartu Stok / Mutasi Item';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter Kartu Stok')
                    ->schema([
                        Forms\Components\Select::make('raw_material_id')
                            ->label('Item')
                            ->options(RawMaterial::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Gudang')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->default(now()),
                    ])
                    ->columns(4),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $rawMaterialId = $this->data['raw_material_id'] ?? null;
        $warehouseId = $this->data['warehouse_id'] ?? null;
        $startDate = $this->data['start_date'] ?? null;
        $endDate = $this->data['end_date'] ?? null;

        return $table
            ->query(
                StockLedger::query()
                    ->when($rawMaterialId, fn ($q) => $q->where('raw_material_id', $rawMaterialId))
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
                    ->with(['rawMaterial.unit'])
                    ->orderBy('transaction_date')
                    ->orderBy('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Jenis Transaksi')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'stock_in' => 'Barang Masuk',
                        'stock_out' => 'Barang Keluar',
                        'transfer_in' => 'Transfer Masuk',
                        'transfer_out' => 'Transfer Keluar',
                        'opname_adjustment' => 'Penyesuaian Opname',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'stock_in', 'transfer_in' => 'success',
                        'stock_out', 'transfer_out' => 'danger',
                        'opname_adjustment' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('direction')
                    ->label('Arah')
                    ->formatStateUsing(fn (string $state): string => $state === 'in' ? 'Masuk' : 'Keluar')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'in' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    ),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('running_qty_balance')
                    ->label('Saldo Qty')
                    ->alignRight()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->rawMaterial->unit->symbol
                    ),
                Tables\Columns\TextColumn::make('running_avg_cost')
                    ->label('Avg Cost')
                    ->money('IDR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('running_asset_value')
                    ->label('Nilai Aset')
                    ->money('IDR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->wrap()
                    ->toggleable(),
            ])
            ->paginated([25, 50, 100]);
    }

    public function hasData(): bool
    {
        return !empty($this->data['raw_material_id']) && !empty($this->data['warehouse_id']);
    }
}
