<?php

namespace App\Filament\Pages\Reports;

use App\Models\StockOpname;
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

class StockOpnameReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.reports.stock-opname-report';

    protected static ?string $navigationLabel = 'Laporan Stock Opname';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 7;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Laporan Stock Opname & Selisih';
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
                StockOpname::query()
                    ->where('status', 'finalized')
                    ->with(['warehouse', 'details'])
                    ->whereBetween('opname_date', [$startDate, $endDate])
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->orderBy('opname_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('opname_number')
                    ->label('No. Opname')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('opname_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('finalized_at')
                    ->label('Finalisasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jumlah Item')
                    ->counts('details')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_difference_value')
                    ->label('Total Selisih Nilai')
                    ->money('IDR')
                    ->alignRight()
                    ->getStateUsing(fn ($record) => $record->details->sum('difference_value'))
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->using(fn ($query) => $query->get()->sum(fn ($r) => $r->details->sum('difference_value')))
                            ->money('IDR')
                            ->label('Grand Total'),
                    ]),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort('opname_date', 'desc');
    }
}
