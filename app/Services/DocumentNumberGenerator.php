<?php

namespace App\Services;

use App\Models\DocumentNumberCounter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentNumberGenerator
{
    private array $prefixes = [
        'stock_in' => 'SIN',
        'stock_out' => 'SOUT',
        'stock_transfer' => 'TRF',
        'stock_opname' => 'OPN',
    ];

    public function generate(string $documentType, \Carbon\Carbon $transactionDate): string
    {
        if (!isset($this->prefixes[$documentType])) {
            throw new InvalidArgumentException("Unknown document type: {$documentType}");
        }

        $prefix = $this->prefixes[$documentType];
        $period = $transactionDate->format('Ym');

        $nextNumber = DB::transaction(function () use ($documentType, $period) {
            $counter = DocumentNumberCounter::where('document_type', $documentType)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                try {
                    $counter = DocumentNumberCounter::create([
                        'document_type' => $documentType,
                        'period' => $period,
                        'last_number' => 0,
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    $counter = DocumentNumberCounter::where('document_type', $documentType)
                        ->where('period', $period)
                        ->lockForUpdate()
                        ->firstOrFail();
                }
            }

            $counter->increment('last_number');

            return $counter->fresh()->last_number;
        }, 5);

        return sprintf('%s-%s%04d', $prefix, $period, $nextNumber);
    }

    public function setPrefixes(array $prefixes): void
    {
        $this->prefixes = array_merge($this->prefixes, $prefixes);
    }
}
