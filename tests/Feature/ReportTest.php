<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportTest extends TestCase
{
    // TC-10-01
    public function test_report_index_loads(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get('/admin/reports')->assertStatus(200);
    }

    // TC-10-03: เดือนไม่มีข้อมูล แสดงได้ไม่ error
    public function test_report_with_no_data_shows_zero(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->get('/admin/reports?month=01&year=2000')
             ->assertStatus(200)
             ->assertSee('0.00');
    }

    // TC-10-04: export CSV
    public function test_report_export_excel_returns_csv(): void
    {
        $this->actingAs($this->makeAdmin());
        $response = $this->get('/admin/reports/export-excel?month=' . now()->format('m') . '&year=' . now()->format('Y'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // TC-10-05: export PDF
    public function test_report_export_pdf_returns_pdf(): void
    {
        $this->actingAs($this->makeAdmin());
        $response = $this->get('/admin/reports/export-pdf?month=' . now()->format('m') . '&year=' . now()->format('Y'));
        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', strtolower($response->headers->get('Content-Type')));
    }
}
