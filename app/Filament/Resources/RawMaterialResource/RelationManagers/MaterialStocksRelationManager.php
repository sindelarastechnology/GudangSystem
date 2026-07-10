<?php

namespace App\Filament\Resources\RawMaterialResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'materialStocks';

    protected static ?string $title = 'Stok per Gudang';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('min_stock')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_avg_cost')
                    ->label('Harga Rata-rata')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('current_asset_value')
                    ->label('Nilai Aset')
                    ->money('IDR'),
                Tables\Columns\TextInputColumn::make('min_stock')
                    ->label('Min. Stok'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
