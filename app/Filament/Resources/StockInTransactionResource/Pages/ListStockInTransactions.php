<?php

namespace App\Filament\Resources\StockInTransactionResource\Pages;

use App\Filament\Resources\StockInTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockInTransactions extends ListRecords
{
    protected static string $resource = StockInTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
