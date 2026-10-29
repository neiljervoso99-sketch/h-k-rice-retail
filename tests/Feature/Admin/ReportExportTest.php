<?php

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_date_range_filters_the_report(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $this->createCompletedOrder($customer, 'IN-RANGE', '2026-03-05 12:00:00', 150);
        $this->createCompletedOrder($customer, 'OUT-OF-RANGE', '2026-03-20 12:00:00', 275);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-10',
            ]))
            ->assertOk()
            ->assertSee('2026-03-01')
            ->assertSee('2026-03-10')
            ->assertSee('₱150.00')
            ->assertSee('IN-RANGE')
            ->assertDontSee('OUT-OF-RANGE');
    }

    public function test_custom_date_range_is_validated(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'start_date' => '2026-03-12',
                'end_date' => '2026-03-01',
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors('end_date');
    }

    public function test_csv_excel_and_pdf_exports_respect_the_selected_date_range(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createCompletedOrder($customer, 'REPORT-1001', '2026-03-05 12:00:00', 150);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Report Rice',
            'brand' => 'H&K',
            'unit_type' => 'kg',
            'quantity' => 2,
            'quantity_kg' => 2,
            'quantity_unit' => 'kg',
            'unit_price' => 75,
            'line_total' => 150,
            'unit_cost' => 50,
        ]);
        Expense::create([
            'category' => 'Supplies',
            'description' => 'Report supplies',
            'amount' => 10,
            'spent_at' => '2026-03-05',
        ]);

        $filters = ['start_date' => '2026-03-01', 'end_date' => '2026-03-10'];

        $csvResponse = $this->actingAs($admin)
            ->get(route('admin.reports.export', $filters))
            ->assertOk()
            ->assertDownload('sales-profit-2026-03-01-to-2026-03-10.csv');
        $csvContent = $csvResponse->streamedContent();
        $this->assertStringContainsString('REPORT-1001', $csvContent);
        $this->assertStringContainsString('2026-03-01', $csvContent);

        $excelResponse = $this->get(route('admin.reports.export.excel', $filters))
            ->assertOk()
            ->assertDownload('sales-profit-2026-03-01-to-2026-03-10.xlsx')
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
        $excelPath = $excelResponse->baseResponse->getFile()->getPathname();
        $spreadsheet = IOFactory::load($excelPath);
        $this->assertSame('Summary', $spreadsheet->getSheet(0)->getTitle());
        $this->assertSame('Product Profit', $spreadsheet->getSheet(1)->getTitle());
        $this->assertSame('Transactions', $spreadsheet->getSheet(2)->getTitle());
        $this->assertSame('REPORT-1001', $spreadsheet->getSheet(2)->getCell('A2')->getValue());
        $spreadsheet->disconnectWorksheets();
        @unlink($excelPath);

        $pdfResponse = $this->get(route('admin.reports.export.pdf', $filters))
            ->assertOk()
            ->assertDownload('sales-profit-2026-03-01-to-2026-03-10.pdf')
            ->assertHeader('content-type', 'application/pdf');
        $pdfContent = $pdfResponse->streamedContent();
        $this->assertStringStartsWith('%PDF-', $pdfContent);
        $this->assertGreaterThan(1000, strlen($pdfContent));
    }

    private function createCompletedOrder(
        User $customer,
        string $orderNumber,
        string $completedAt,
        float $total,
    ): Order {
        return Order::create([
            'order_number' => $orderNumber,
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
            'subtotal' => $total,
            'total' => $total,
            'completed_at' => $completedAt,
        ]);
    }
}
