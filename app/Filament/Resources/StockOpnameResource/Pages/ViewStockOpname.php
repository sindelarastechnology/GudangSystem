<?php

namespace App\Filament\Resources\StockOpnameResource\Pages;

use App\Filament\Resources\StockOpnameResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewStockOpname extends ViewRecord
{
    protected static string $resource = StockOpnameResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Opname')
                    ->schema([
                        Infolists\Components\TextEntry::make('opname_number')
                            ->label('No. Opname'),
                        Infolists\Components\TextEntry::make('opname_date')
                            ->label('Tanggal')
                            ->date('d M Y'),
                        Infolists\Components\TextEntry::make('warehouse.name')
                            ->label('Gudang'),
                        Infolists\Components\TextEntry::make('status')
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
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Mulai')
                            ->dateTime('d M Y H:i'),
                        Infolists\Components\TextEntry::make('finalized_at')
                            ->label('Finalisasi')
                            ->dateTime('d M Y H:i')
                            ->visible(fn ($record) => $record->isFinalized()),
                        Infolists\Components\TextEntry::make('cancelled_at')
                            ->label('Dibatalkan')
                            ->dateTime('d M Y H:i')
                            ->visible(fn ($record) => $record->isCancelled()),
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->visible(fn ($state) => filled($state)),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Detail Item')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('rawMaterial.code')
                                    ->label('Kode'),
                                Infolists\Components\TextEntry::make('rawMaterial.name')
                                    ->label('Nama Item'),
                                Infolists\Components\TextEntry::make('system_qty')
                                    ->label('Qty Sistem')
                                    ->suffix(fn ($record) => ' ' . $record->rawMaterial->unit->symbol),
                                Infolists\Components\TextEntry::make('physical_qty_base')
                                    ->label('Qty Fisik')
                                    ->suffix(fn ($record) => ' ' . $record->rawMaterial->unit->symbol),
                                Infolists\Components\TextEntry::make('difference_qty')
                                    ->label('Selisih')
                                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                                    ->suffix(fn ($record) => ' ' . $record->rawMaterial->unit->symbol),
                                Infolists\Components\TextEntry::make('difference_value')
                                    ->label('Nilai Selisih')
                                    ->money('IDR'),
                            ])
                            ->columns(6)
                            ->visible(fn ($record) => $record->isFinalized()),
                    ])
                    ->visible(fn ($record) => $record->isFinalized()),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url(static::getResource()::getUrl('index')),
        ];
    }
}
