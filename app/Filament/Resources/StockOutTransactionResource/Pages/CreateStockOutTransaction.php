<?php

namespace App\Filament\Resources\StockOutTransactionResource\Pages;

use App\Filament\Resources\StockOutTransactionResource;
use App\Services\StockOutService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateStockOutTransaction extends CreateRecord
{
    protected static string $resource = StockOutTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(StockOutService::class);

        return $service->store(
            warehouseId: $data['warehouse_id'],
            type: $data['type'],
            date: \Carbon\Carbon::parse($data['transaction_date']),
            destination: $data['destination'] ?? null,
            notes: $data['notes'] ?? null,
            createdBy: Auth::user(),
            details: $data['details'],
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
