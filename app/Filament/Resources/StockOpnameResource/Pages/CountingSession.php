<?php

namespace App\Filament\Resources\StockOpnameResource\Pages;

use App\Filament\Resources\StockOpnameResource;
use App\Models\MaterialStock;
use App\Models\RawMaterial;
use App\Services\StockOpnameService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class CountingSession extends ViewRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected static string $view = 'filament.resources.stock-opname.pages.counting-session';

    public array $physicalQty = [];

    public function mount(string|int $record): void
    {
        parent::mount($record);

        $items = $this->getViewData()['items'];
        
        foreach ($items as $item) {
            $this->physicalQty[$item['raw_material_id']] = $item['physical_qty'];
        }
    }

    public function getTitle(): string
    {
        return "Input Fisik: {$this->record->opname_number}";
    }

    public function saveDraft(int $rawMaterialId, float $physicalQty): void
    {
        if ($physicalQty < 0) {
            Notification::make()
                ->danger()
                ->title('Nilai tidak valid')
                ->body('Jumlah fisik tidak boleh negatif.')
                ->send();
            return;
        }

        try {
            $rawMaterial = RawMaterial::findOrFail($rawMaterialId);
            
            $opnameService = app(StockOpnameService::class);
            $opnameService->saveDraftDetails($this->record, [
                [
                    'raw_material_id' => $rawMaterialId,
                    'physical_qty_unit_id' => $rawMaterial->unit_id,
                    'physical_qty' => $physicalQty,
                    'notes' => null,
                ]
            ]);

            Notification::make()
                ->success()
                ->title('Draft tersimpan')
                ->body("Qty fisik untuk {$rawMaterial->name} berhasil disimpan.")
                ->send();

            $this->record->refresh();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal menyimpan draft')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('finalize')
                ->label('Finalisasi Opname')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Finalisasi Stock Opname')
                ->modalDescription('Setelah difinalisasi, stok sistem akan disesuaikan dengan jumlah fisik. Aksi ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Finalisasi')
                ->action(function () {
                    try {
                        $opnameService = app(StockOpnameService::class);
                        $opnameService->finalize($this->record);

                        Notification::make()
                            ->success()
                            ->title('Stock opname berhasil difinalisasi')
                            ->body('Stok sistem telah disesuaikan dengan jumlah fisik.')
                            ->send();

                        return redirect()->to(static::getResource()::getUrl('view', ['record' => $this->record->id]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal finalisasi')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->isCounting()),

            Actions\Action::make('cancel')
                ->label('Batalkan Sesi')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Batalkan Sesi Opname')
                ->modalDescription('Membatalkan sesi opname akan melepas lock gudang tanpa mengubah stok. Data input fisik akan diabaikan.')
                ->modalSubmitActionLabel('Ya, Batalkan')
                ->action(function () {
                    try {
                        $opnameService = app(StockOpnameService::class);
                        $opnameService->cancel($this->record);

                        Notification::make()
                            ->success()
                            ->title('Sesi opname dibatalkan')
                            ->send();

                        return redirect()->to(static::getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal membatalkan')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->isCounting()),
        ];
    }

    public function getViewData(): array
    {
        $existingStocks = MaterialStock::where('warehouse_id', $this->record->warehouse_id)
            ->with(['rawMaterial.unit', 'rawMaterial.materialCategory'])
            ->get();

        $existingDetails = $this->record->details->keyBy('raw_material_id');

        $items = $existingStocks->map(function ($stock) use ($existingDetails) {
            $detail = $existingDetails->get($stock->raw_material_id);

            return [
                'raw_material_id' => $stock->raw_material_id,
                'raw_material_code' => $stock->rawMaterial->code,
                'raw_material_name' => $stock->rawMaterial->name,
                'unit_symbol' => $stock->rawMaterial->unit->symbol,
                'system_qty' => $stock->current_stock,
                'physical_qty' => $detail?->physical_qty ?? 0,
                'detail_id' => $detail?->id,
            ];
        })->toArray();

        return [
            'items' => $items,
            'opname' => $this->record,
            'warehouse' => $this->record->warehouse,
        ];
    }
}
