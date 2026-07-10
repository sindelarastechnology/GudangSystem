<?php

namespace App\Filament\Pages\Reports;

use App\Models\StockInTransaction;
use App\Models\Supplier;
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

class StockInSummaryReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.pages.reports.stock-in-summary-report';

    protected static ?string $navigationLabel = 'Rekap Barang Masuk';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Laporan Rekap Barang Masuk';
    }

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth(),
            'end_date' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter Periode')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->required()
                            ->live(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Gudang')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->live(),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable()
                            ->live(),
                    ])
                    ->columns(4),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $startDate = $this->data['start_date'] ?? now()->startOfMonth();
        $endDate = $this->data['end_date'] ?? now();
        $warehouseId = $this->data['warehouse_id'] ?? null;
        $supplierId = $this->data['supplier_id'] ?? null;

        return $table
            ->query(
                StockInTransaction::query()
                    ->with(['warehouse', 'supplier', 'details.rawMaterial.unit'])
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->orderBy('transaction_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Pembelian',
                        'production_return' => 'Retur Produksi',
                        'adjustment_add' => 'Penyesuaian Tambah',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jumlah Item')
                    ->counts('details')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Total Nilai')
                    ->money('IDR')
                    ->alignRight()
                    ->getStateUsing(fn ($record) => $record->details->sum('subtotal')),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort('transaction_date', 'desc');
    }
}
