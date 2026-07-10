<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\StockTransfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Header')
                        ->schema([
                            Forms\Components\DatePicker::make('transfer_date')
                                ->label('Tanggal Transfer')
                                ->required()
                                ->default(now()),
                            Forms\Components\Select::make('from_warehouse_id')
                                ->label('Gudang Asal')
                                ->relationship('fromWarehouse', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('to_warehouse_id')
                                ->label('Gudang Tujuan')
                                ->relationship('toWarehouse', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->different('from_warehouse_id'),
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
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('No. Transfer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromWarehouse.name')
                    ->label('Dari Gudang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('toWarehouse.name')
                    ->label('Ke Gudang')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('from_warehouse_id')
                    ->label('Dari Gudang')
                    ->relationship('fromWarehouse', 'name'),
                Tables\Filters\SelectFilter::make('to_warehouse_id')
                    ->label('Ke Gudang')
                    ->relationship('toWarehouse', 'name'),
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
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'view' => Pages\ViewStockTransfer::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'fromWarehouse', 'toWarehouse', 'createdBy', 'details.rawMaterial', 'details.unit',
        ]);
    }
}
