<?php

namespace Tests\Unit;

use App\Models\CompanySetting;
use App\Services\Sales\SalesDocumentGstDetailsService;
use App\Services\Support\IndianAmountInWordsService;
use PHPUnit\Framework\TestCase;

class SalesDocumentGstDetailsServiceTest extends TestCase
{
    private function service(): SalesDocumentGstDetailsService
    {
        return new SalesDocumentGstDetailsService(new IndianAmountInWordsService);
    }

    public function test_amount_in_words_uses_indian_numbering(): void
    {
        $words = new IndianAmountInWordsService;

        $this->assertSame('Rs. One Hundred Eighty Only', $words->rupees(180));
        $this->assertSame('Rs. One Lakh Twenty Three Thousand Four Hundred Fifty Six Only', $words->rupees(123456));
        $this->assertSame('Rs. Two Crore Fifty Lakh Only', $words->rupees(25000000));
        $this->assertSame('Rs. Ten and Fifty Paise Only', $words->rupees(10.5));
        $this->assertSame('Rs. Zero Only', $words->rupees(0));
    }

    public function test_tax_summary_groups_by_rate_from_gst_inclusive_lines(): void
    {
        $items = [
            ['gst_percent' => 18, 'gst_amount' => 360, 'line_total' => 2360],
            ['gst_percent' => 18, 'gst_amount' => 180, 'line_total' => 1180],
            ['gst_percent' => 5, 'gst_amount' => 5, 'line_total' => 105],
        ];

        $summary = $this->service()->taxSummary($items, 3645, 'Maharashtra', null);

        $this->assertCount(2, $summary['rows']);
        $this->assertSame(5.0, $summary['rows'][0]['rate']);
        $this->assertSame(3000.0, $summary['rows'][1]['taxable']);
        $this->assertSame(540.0, $summary['rows'][1]['tax']);
        $this->assertSame(3100.0, $summary['taxable']);
        $this->assertSame(545.0, $summary['tax']);
    }

    public function test_supply_state_decides_between_cgst_sgst_and_igst(): void
    {
        $company = new CompanySetting(['address' => 'MIDC, Jalna, Maharashtra 431203']);

        $this->assertTrue($this->service()->taxSummary([], 0, 'Maharashtra', $company)['intra_state']);
        $this->assertFalse($this->service()->taxSummary([], 0, 'Gujarat', $company)['intra_state']);
        // Unknown state or company address: default to CGST + SGST.
        $this->assertTrue($this->service()->taxSummary([], 0, '', $company)['intra_state']);
        $this->assertTrue($this->service()->taxSummary([], 0, 'Gujarat', null)['intra_state']);
    }
}
