<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Models\StockOpname;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Stock Opname';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('opname_date')
                    ->label('Tanggal Opname')
                    ->required(),
                Forms\Components\Select::make('warehouse_id')
                    ->label('Gudang')
                    ->relationship('warehouse', 'name')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'counting' => 'warning',
                        'finalized' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'counting' => 'Menghitung',
                        'finalized' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    }),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('finalized_at')
                    ->label('Finalisasi')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'counting' => 'Menghitung',
                        'finalized' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->relationship('warehouse', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('continue')
                    ->label('Lanjutkan')
                    ->icon('heroicon-o-arrow-right')
                    ->url(fn (StockOpname $record): string => 
                        StockOpnameResource::getUrl('counting', ['record' => $record->id])
                    )
                    ->visible(fn (StockOpname $record): bool => $record->isCounting()),
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->visible(fn (StockOpname $record): bool => 
                        $record->isFinalized() || $record->isCancelled()
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'counting' => Pages\CountingSession::route('/{record}/counting'),
            'view' => Pages\ViewStockOpname::route('/{record}'),
        ];
    }
}
