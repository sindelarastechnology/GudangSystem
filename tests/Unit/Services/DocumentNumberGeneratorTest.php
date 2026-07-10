<?php

namespace Tests\Unit\Services;

use App\Models\DocumentNumberCounter;
use App\Services\DocumentNumberGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private DocumentNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = app(DocumentNumberGenerator::class);
    }

    public function test_generates_stock_in_number(): void
    {
        $number = $this->generator->generate('stock_in', Carbon::parse('2026-07-15'));
        $this->assertEquals('SIN-2026070001', $number);
    }

    public function test_generates_stock_out_number(): void
    {
        $number = $this->generator->generate('stock_out', Carbon::parse('2026-07-15'));
        $this->assertEquals('SOUT-2026070001', $number);
    }

    public function test_generates_transfer_number(): void
    {
        $number = $this->generator->generate('stock_transfer', Carbon::parse('2026-07-15'));
        $this->assertEquals('TRF-2026070001', $number);
    }

    public function test_generates_opname_number(): void
    {
        $number = $this->generator->generate('stock_opname', Carbon::parse('2026-07-15'));
        $this->assertEquals('OPN-2026070001', $number);
    }

    public function test_increments_counter(): void
    {
        $date = Carbon::parse('2026-07-15');
        $this->assertEquals('SIN-2026070001', $this->generator->generate('stock_in', $date));
        $this->assertEquals('SIN-2026070002', $this->generator->generate('stock_in', $date));
        $this->assertEquals('SIN-2026070003', $this->generator->generate('stock_in', $date));
    }

    public function test_uses_period_from_transaction_date(): void
    {
        $this->assertEquals('SIN-2026070001', $this->generator->generate('stock_in', Carbon::parse('2026-07-01')));
        $this->assertEquals('SIN-2026080001', $this->generator->generate('stock_in', Carbon::parse('2026-08-01')));
    }

    public function test_unknown_type_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate('unknown_type', Carbon::now());
    }
}
