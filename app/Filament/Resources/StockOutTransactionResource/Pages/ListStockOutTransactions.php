<?php

namespace App\Filament\Resources\StockOutTransactionResource\Pages;

use App\Filament\Resources\StockOutTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockOutTransactions extends ListRecords
{
    protected static string $resource = StockOutTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
