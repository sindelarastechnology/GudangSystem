<?php

namespace App\Filament\Pages\Reports;

use App\Models\StockTransfer;
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

class TransferReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.pages.reports.transfer-report';

    protected static ?string $navigationLabel = 'Rekap Transfer';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Laporan Transfer Antar Gudang';
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
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $startDate = $this->data['start_date'] ?? now()->startOfMonth();
        $endDate = $this->data['end_date'] ?? now();

        return $table
            ->query(
                StockTransfer::query()
                    ->with(['fromWarehouse', 'toWarehouse', 'details.rawMaterial.unit'])
                    ->whereBetween('transfer_date', [$startDate, $endDate])
                    ->orderBy('transfer_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('No. Transfer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromWarehouse.name')
                    ->label('Dari Gudang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('toWarehouse.name')
                    ->label('Ke Gudang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jumlah Item')
                    ->counts('details')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Total Nilai Transfer')
                    ->money('IDR')
                    ->alignRight()
                    ->getStateUsing(fn ($record) => $record->details->sum(fn ($d) => $d->qty_base * $d->cost_at_transfer))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->using(fn ($query) => $query->get()->sum(fn ($r) => 
                                $r->details->sum(fn ($d) => $d->qty_base * $d->cost_at_transfer)
                            ))
                            ->money('IDR')
                            ->label('Grand Total'),
                    ]),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort('transfer_date', 'desc');
    }
}
