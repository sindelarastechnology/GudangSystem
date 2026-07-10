<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockInTransactionResource\Pages;
use App\Models\StockInTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockInTransactionResource extends Resource
{
    protected static ?string $model = StockInTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Header')
                        ->schema([
                            Forms\Components\DatePicker::make('transaction_date')
                                ->label('Tanggal')
                                ->required()
                                ->default(now()),
                            Forms\Components\Select::make('warehouse_id')
                                ->label('Gudang')
                                ->relationship('warehouse', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('supplier_id')
                                ->label('Supplier')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->required(fn (Forms\Get $get): bool => $get('type') === 'purchase'),
                            Forms\Components\Select::make('type')
                                ->label('Tipe')
                                ->required()
                                ->options([
                                    'purchase' => 'Pembelian',
                                    'production_return' => 'Retur Produksi',
                                    'adjustment_add' => 'Penyesuaian Tambah',
                                ]),
                            Forms\Components\TextInput::make('reference_number')
                                ->label('No. Referensi')
                                ->maxLength(50),
                            Forms\Components\FileUpload::make('attachment')
                                ->label('Lampiran')
                                ->directory('stock-in')
                                ->maxSize(2048),
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan'),
                        ]),
                    Forms\Components\Wizard\Step::make('Item')
                        ->schema([
                            Forms\Components\Repeater::make('details')
                                ->label('Detail Barang')
                                ->schema([
                                    Forms\Components\Select::make('raw_material_id')
                                        ->label('Bahan Baku')
                                        ->options(\App\Models\RawMaterial::where('is_active', true)->pluck('name', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('unit_id')
                                        ->label('Satuan')
                                        ->options(\App\Models\Unit::pluck('name', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\TextInput::make('qty')
                                        ->label('Qty')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0.0001),
                                    Forms\Components\TextInput::make('unit_price')
                                        ->label('Harga Satuan')
                                        ->required(fn (Forms\Get $get): bool => $get('../../type') !== 'adjustment_add')
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix('Rp'),
                                    Forms\Components\TextInput::make('notes')
                                        ->label('Catatan'),
                                ])
                                ->columns(2)
                                ->required()
                                ->minItems(1),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'purchase' => 'Pembelian',
                        'production_return' => 'Retur Produksi',
                        'adjustment_add' => 'Penyesuaian Tambah',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->relationship('warehouse', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'purchase' => 'Pembelian',
                        'production_return' => 'Retur Produksi',
                        'adjustment_add' => 'Penyesuaian Tambah',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockInTransactions::route('/'),
            'create' => Pages\CreateStockInTransaction::route('/create'),
            'view' => Pages\ViewStockInTransaction::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'warehouse', 'supplier', 'createdBy', 'details.rawMaterial', 'details.unit',
        ]);
    }
}
