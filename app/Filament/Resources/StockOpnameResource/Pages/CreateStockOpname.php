<?php

namespace App\Filament\Resources\StockOpnameResource\Pages;

use App\Filament\Resources\StockOpnameResource;
use App\Services\StockOpnameService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $opnameService = app(StockOpnameService::class);

        $opname = $opnameService->openSession(
            warehouseId: $data['warehouse_id'],
            date: \Carbon\Carbon::parse($data['opname_date']),
            notes: $data['notes'] ?? null,
            createdBy: auth()->user(),
        );

        return $opname;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('counting', ['record' => $this->record->id]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Sesi stock opname berhasil dibuat. Silakan input jumlah fisik.';
    }
}
