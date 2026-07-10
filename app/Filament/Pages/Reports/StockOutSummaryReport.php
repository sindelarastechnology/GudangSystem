<?php

namespace App\Filament\Pages\Reports;

use App\Models\StockOutTransaction;
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

class StockOutSummaryReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string $view = 'filament.pages.reports.stock-out-summary-report';

    protected static ?string $navigationLabel = 'Rekap Barang Keluar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Laporan Rekap Barang Keluar / HPP';
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
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $startDate = $this->data['start_date'] ?? now()->startOfMonth();
        $endDate = $this->data['end_date'] ?? now();
        $warehouseId = $this->data['warehouse_id'] ?? null;

        return $table
            ->query(
                StockOutTransaction::query()
                    ->with(['warehouse', 'details.rawMaterial.unit'])
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'production_usage' => 'Pemakaian Produksi',
                        'supplier_return' => 'Retur ke Supplier',
                        'adjustment_reduce' => 'Penyesuaian Kurang',
                        'damaged_lost' => 'Rusak/Hilang',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production_usage' => 'primary',
                        'supplier_return' => 'warning',
                        'adjustment_reduce' => 'info',
                        'damaged_lost' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('destination')
                    ->label('Tujuan')
                    ->default('-')
                    ->wrap(),
                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jumlah Item')
                    ->counts('details')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_hpp')
                    ->label('Total HPP')
                    ->money('IDR')
                    ->alignRight()
                    ->getStateUsing(fn ($record) => $record->details->sum('subtotal_hpp')),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort('transaction_date', 'desc');
    }
}
