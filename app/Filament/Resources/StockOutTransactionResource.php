<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOutTransactionResource\Pages;
use App\Models\StockOutTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockOutTransactionResource extends Resource
{
    protected static ?string $model = StockOutTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

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
                                ->label('Gudang Asal')
                                ->relationship('warehouse', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('type')
                                ->label('Tipe')
                                ->required()
                                ->options([
                                    'production_usage' => 'Pemakaian Produksi',
                                    'supplier_return' => 'Retur ke Supplier',
                                    'adjustment_reduce' => 'Penyesuaian Kurang',
                                    'damaged_lost' => 'Rusak/Hilang',
                                ]),
                            Forms\Components\TextInput::make('destination')
                                ->label('Tujuan')
                                ->maxLength(150),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'production_usage' => 'Pemakaian Produksi',
                        'supplier_return' => 'Retur ke Supplier',
                        'adjustment_reduce' => 'Penyesuaian Kurang',
                        'damaged_lost' => 'Rusak/Hilang',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('destination')
                    ->label('Tujuan')
                    ->searchable(),
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
                        'production_usage' => 'Pemakaian Produksi',
                        'supplier_return' => 'Retur ke Supplier',
                        'adjustment_reduce' => 'Penyesuaian Kurang',
                        'damaged_lost' => 'Rusak/Hilang',
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
            'index' => Pages\ListStockOutTransactions::route('/'),
            'create' => Pages\CreateStockOutTransaction::route('/create'),
            'view' => Pages\ViewStockOutTransaction::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'warehouse', 'createdBy', 'details.rawMaterial', 'details.unit',
        ]);
    }
}
