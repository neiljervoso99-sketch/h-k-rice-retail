<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    private function reportDates(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:daily,weekly,monthly,yearly,custom'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'start_date' => ['nullable', 'date_format:Y-m-d', 'required_with:end_date'],
            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:start_date',
                'after_or_equal:start_date',
            ],
        ]);

        if (! empty($validated['start_date']) || ! empty($validated['end_date'])) {
            $start = Carbon::createFromFormat('Y-m-d', $validated['start_date'])->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $validated['end_date'])->endOfDay();

            return [
                'custom',
                $start->toDateString(),
                $start,
                $end,
                $start->toDateString(),
                $end->toDateString(),
            ];
        }

        $period = $validated['period'] ?? 'daily';
        if ($period === 'custom') {
            throw ValidationException::withMessages([
                'start_date' => 'Choose both dates for a custom report range.',
            ]);
        }

        $selectedDate = $validated['date'] ?? Carbon::today()->toDateString();
        $referenceDate = Carbon::createFromFormat('Y-m-d', $selectedDate)->startOfDay();
        $selectedDate = $referenceDate->toDateString();

        switch ($period) {
            case 'weekly':
                $start = $referenceDate->copy()->startOfWeek();
                $end = $referenceDate->copy()->endOfWeek();
                break;
            case 'monthly':
                $start = $referenceDate->copy()->startOfMonth();
                $end = $referenceDate->copy()->endOfMonth();
                break;
            case 'yearly':
                $start = $referenceDate->copy()->startOfYear();
                $end = $referenceDate->copy()->endOfYear();
                break;
            default:
                $start = $referenceDate->copy()->startOfDay();
                $end = $referenceDate->copy()->endOfDay();
        }

        return [
            $period,
            $selectedDate,
            $start,
            $end,
            $start->toDateString(),
            $end->toDateString(),
        ];
    }

    private function completedOrders(Carbon $start, Carbon $end)
    {
        return Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $start->toDateTimeString(),
                $end->toDateTimeString(),
            ]);
    }

    private function completedItems(Carbon $start, Carbon $end)
    {
        return OrderItem::query()
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ]);
            });
    }

    private function expenseTotal(Carbon $start, Carbon $end): float
    {
        return (float) Expense::query()
            ->whereBetween('spent_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount');
    }

    private function varietyBreakdown(Carbon $start, Carbon $end)
    {
        return $this->completedItems($start, $end)
            ->whereNotNull('unit_cost')
            ->get([
                'product_name',
                'brand',
                'unit_type',
                'quantity',
                'line_total',
                'unit_cost',
            ])
            ->groupBy(function ($item) {
                return trim((string) $item->product_name)
                    .'|'.trim((string) $item->brand);
            })
            ->map(function ($items) {
                $first = $items->first();
                $quantity = (float) $items->sum('quantity');
                $sales = (float) $items->sum('line_total');
                $cost = (float) $items->sum(function ($item) {
                    return (float) $item->unit_cost * (float) $item->quantity;
                });

                return (object) [
                    'product_name' => $first->product_name ?: 'Unnamed product',
                    'brand' => $first->brand ?: '',
                    'quantity' => $quantity,
                    'sales' => $sales,
                    'cost' => $cost,
                    'profit' => $sales - $cost,
                ];
            })
            ->sortBy('product_name')
            ->values();
    }

    public function index(Request $request)
    {
        [$period, $selectedDate, $start, $end, $startDate, $endDate] = $this->reportDates($request);

        /*
         * Completed orders within the selected period.
         */
        $completedQuery = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [
                $start->toDateTimeString(),
                $end->toDateTimeString(),
            ]);

        $salesTotal = (clone $completedQuery)->sum('total');

        $completedOrderCount = (clone $completedQuery)->count();

        /*
         * Item sales and cost only include records with unit_cost.
         */
        $recordedItemQuery = OrderItem::query()
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ]);
            })
            ->whereNotNull('unit_cost');

        $recordedItemSales = (clone $recordedItemQuery)
            ->sum('line_total');

        $recordedItemCost = (clone $recordedItemQuery)
            ->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as aggregate')
            ->value('aggregate');

        $grossProfit = (float) $recordedItemSales
            - (float) $recordedItemCost;

        $itemsWithoutCostCount = OrderItem::query()
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ]);
            })
            ->whereNull('unit_cost')
            ->count();

        /*
         * Expenses within the selected period.
         */
        $expensesTotal = Expense::query()
            ->whereBetween('spent_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount');
        $netProfit = $grossProfit - (float) $expensesTotal;
        $varietyBreakdown = $this->varietyBreakdown($start, $end);

        /*
         * Daily sales breakdown.
         */
        $dailySales = (clone $completedQuery)
            ->selectRaw('
                DATE(completed_at) as sales_date,
                COUNT(*) as order_count,
                SUM(total) as sales_total
            ')
            ->groupByRaw('DATE(completed_at)')
            ->orderByRaw('DATE(completed_at) ASC')
            ->get();

        /*
         * Payment method totals.
         *
         * COD / Cash on Delivery -> Cash
         * Cash                  -> Cash
         * GCash                 -> GCash
         *
         * Other payment values are excluded from this chart rather than
         * incorrectly being counted as Cash.
         */
        $paymentRows = (clone $completedQuery)
            ->where(function ($query) {
                $query->whereRaw("
                    LOWER(REPLACE(TRIM(payment_method), '_', ' ')) IN (
                        'cod',
                        'cash',
                        'cash on delivery',
                        'cash on delivery (cod)'
                    )
                ")->orWhereRaw("
                    LOWER(REPLACE(TRIM(payment_method), '_', ' ')) IN (
                        'gcash',
                        'g cash'
                    )
                ");
            })
            ->selectRaw("
                CASE
                    WHEN LOWER(REPLACE(TRIM(payment_method), '_', ' ')) IN (
                        'cod',
                        'cash',
                        'cash on delivery',
                        'cash on delivery (cod)'
                    ) THEN 'Cash'
                    ELSE 'GCash'
                END as payment_label
            ")
            ->selectRaw('SUM(total) as sales_total')
            ->groupBy('payment_label')
            ->orderByRaw("
                CASE
                    WHEN payment_label = 'Cash' THEN 1
                    ELSE 2
                END
            ")
            ->get();

        $paymentLabels = $paymentRows
            ->pluck('payment_label')
            ->all();

        $paymentData = $paymentRows
            ->pluck('sales_total')
            ->map(fn ($value) => (float) $value)
            ->all();

        /*
         * Completed transaction details, including GCash references.
         */
        $transactionDetails = (clone $completedQuery)
            ->with(['payments'])
            ->orderByDesc('completed_at')
            ->get([
                'id',
                'order_number',
                'completed_at',
                'recipient_name',
                'payment_method',
                'payment_status',
                'total',
            ]);

        return view('admin.reports.index', compact(
            'period',
            'selectedDate',
            'start',
            'end',
            'startDate',
            'endDate',
            'salesTotal',
            'completedOrderCount',
            'recordedItemSales',
            'recordedItemCost',
            'grossProfit',
            'itemsWithoutCostCount',
            'expensesTotal',
            'netProfit',
            'varietyBreakdown',
            'dailySales',
            'paymentLabels',
            'paymentData',
            'transactionDetails'
        ));
    }

    public function export(Request $request)
    {
        [$period, $selectedDate, $start, $end] = $this->reportDates($request);

        $completedQuery = $this->completedOrders($start, $end);
        $salesTotal = (float) (clone $completedQuery)->sum('total');
        $completedOrderCount = (int) (clone $completedQuery)->count();

        $recordedItems = $this->completedItems($start, $end)
            ->whereNotNull('unit_cost');
        $recordedItemSales = (float) (clone $recordedItems)->sum('line_total');
        $recordedItemCost = (float) (clone $recordedItems)
            ->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as aggregate')
            ->value('aggregate');

        $grossProfit = $recordedItemSales - $recordedItemCost;
        $expensesTotal = $this->expenseTotal($start, $end);
        $netProfit = $grossProfit - $expensesTotal;
        $itemsWithoutCostCount = $this->completedItems($start, $end)
            ->whereNull('unit_cost')
            ->count();
        $varieties = $this->varietyBreakdown($start, $end);
        $transactions = (clone $completedQuery)
            ->with(['payments'])
            ->orderBy('completed_at')
            ->get([
                'id',
                'order_number',
                'completed_at',
                'recipient_name',
                'payment_method',
                'payment_status',
                'total',
            ]);
        $filename = $period === 'custom'
            ? 'sales-profit-'.$start->toDateString().'-to-'.$end->toDateString().'.csv'
            : 'sales-profit-'.$period.'-'.$selectedDate.'.csv';

        return response()->streamDownload(function () use (
            $period,
            $selectedDate,
            $start,
            $end,
            $salesTotal,
            $completedOrderCount,
            $recordedItemSales,
            $recordedItemCost,
            $grossProfit,
            $expensesTotal,
            $netProfit,
            $itemsWithoutCostCount,
            $varieties,
            $transactions
        ) {
            $file = fopen('php://output', 'w');

            if ($file === false) {
                throw new \RuntimeException('Unable to open report output stream.');
            }

            $writeRow = static function (array $row) use ($file): void {
                foreach ($row as &$value) {
                    if (is_string($value) && preg_match('/^[\t\r ]*[=+\-@]/', $value)) {
                        $value = "'".$value;
                    }
                }
                unset($value);

                fputcsv($file, $row);
            };

            fwrite($file, "\xEF\xBB\xBF");
            $writeRow(['Sales and Profit Report']);
            $writeRow(['Period', $period]);
            $writeRow(['Reference date', $selectedDate]);
            $writeRow(['Start', $start->toDateTimeString()]);
            $writeRow(['End', $end->toDateTimeString()]);
            $writeRow([]);

            $writeRow(['Summary', 'Amount / Count']);
            $writeRow(['Total sales (completed orders)', $salesTotal]);
            $writeRow(['Completed orders', $completedOrderCount]);
            $writeRow(['Recorded item sales', $recordedItemSales]);
            $writeRow(['Recorded item cost', $recordedItemCost]);
            $writeRow(['Recorded gross profit', $grossProfit]);
            $writeRow(['Recorded expenses', $expensesTotal]);
            $writeRow(['Estimated net profit', $netProfit]);
            $writeRow(['Items without recorded cost', $itemsWithoutCostCount]);
            $writeRow([]);
            $writeRow([
                'Rice variety / product',
                'Brand',
                'Quantity (mixed units)',
                'Recorded item sales',
                'Recorded item cost',
                'Estimated profit',
            ]);

            foreach ($varieties as $variety) {
                $writeRow([
                    $variety->product_name,
                    $variety->brand,
                    $variety->quantity,
                    $variety->sales,
                    $variety->cost,
                    $variety->profit,
                ]);
            }

            $writeRow([]);
            $writeRow(['Completed Transaction Details']);
            $writeRow([
                'Order Number',
                'Date Completed',
                'Customer',
                'Payment Method',
                'Payment Status',
                'GCash Reference Number',
                'Transaction Total',
            ]);

            foreach ($transactions as $transaction) {
                $referenceNumber = '';

                if (strtolower(trim((string) $transaction->payment_method)) === 'gcash') {
                    $payment = $transaction->payments->first(
                        fn ($payment) => strtolower(trim((string) $payment->method)) === 'gcash'
                    );

                    $referenceNumber = $payment?->reference_number ?? '';
                }

                $writeRow([
                    $transaction->order_number,
                    $transaction->completed_at,
                    $transaction->recipient_name ?: 'N/A',
                    $transaction->payment_method ?: 'N/A',
                    $transaction->payment_status ?: 'Unknown',
                    $referenceNumber,
                    $transaction->total,
                ]);
            }

            $writeRow([]);
            $writeRow([
                'Note',
                'Profit includes only completed order items with recorded unit cost.',
            ]);
            $writeRow([
                'Note',
                'Quantities may mix sacks and kilograms; use sales, cost and profit for financial totals.',
            ]);

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportData(Request $request): array
    {
        [$period, $selectedDate, $start, $end, $startDate, $endDate] =
            $this->reportDates($request);

        $completedQuery = $this->completedOrders($start, $end);
        $recordedItems = $this->completedItems($start, $end)
            ->whereNotNull('unit_cost');
        $recordedItemSales = (float) (clone $recordedItems)->sum('line_total');
        $recordedItemCost = (float) (clone $recordedItems)
            ->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as aggregate')
            ->value('aggregate');
        $grossProfit = $recordedItemSales - $recordedItemCost;
        $expensesTotal = $this->expenseTotal($start, $end);
        $itemsWithoutCostCount = $this->completedItems($start, $end)
            ->whereNull('unit_cost')
            ->count();

        $transactions = (clone $completedQuery)
            ->with('payments')
            ->orderBy('completed_at')
            ->get([
                'id',
                'order_number',
                'completed_at',
                'recipient_name',
                'payment_method',
                'payment_status',
                'total',
            ]);

        $dailySales = (clone $completedQuery)
            ->selectRaw('DATE(completed_at) as sales_date, COUNT(*) as order_count, SUM(total) as sales_total')
            ->groupByRaw('DATE(completed_at)')
            ->orderByRaw('DATE(completed_at)')
            ->get();

        return [
            'period' => $period,
            'selectedDate' => $selectedDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesTotal' => (float) (clone $completedQuery)->sum('total'),
            'completedOrderCount' => (int) (clone $completedQuery)->count(),
            'recordedItemSales' => $recordedItemSales,
            'recordedItemCost' => $recordedItemCost,
            'grossProfit' => $grossProfit,
            'expensesTotal' => $expensesTotal,
            'netProfit' => $grossProfit - $expensesTotal,
            'itemsWithoutCostCount' => $itemsWithoutCostCount,
            'varieties' => $this->varietyBreakdown($start, $end),
            'transactions' => $transactions,
            'dailySales' => $dailySales,
        ];
    }

    public function exportExcel(Request $request)
    {
        $data = $this->exportData($request);
        $filename = 'sales-profit-'.$data['startDate'].'-to-'.$data['endDate'].'.xlsx';
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Sales and Profit Report')
            ->setSubject('Sales, profit, expenses, and completed transactions');

        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Summary');
        $this->writeWorksheet($summary, [
            ['Sales and Profit Report'],
            ['Start date', $data['startDate']],
            ['End date', $data['endDate']],
            [],
            ['Metric', 'Value'],
            ['Total sales', $data['salesTotal']],
            ['Completed orders', $data['completedOrderCount']],
            ['Recorded item sales', $data['recordedItemSales']],
            ['Recorded item cost', $data['recordedItemCost']],
            ['Recorded gross profit', $data['grossProfit']],
            ['Recorded expenses', $data['expensesTotal']],
            ['Estimated net profit', $data['netProfit']],
            ['Items without recorded cost', $data['itemsWithoutCostCount']],
        ]);

        $products = $spreadsheet->createSheet();
        $products->setTitle('Product Profit');
        $productRows = [[
            'Product',
            'Brand',
            'Quantity (mixed units)',
            'Recorded sales',
            'Recorded cost',
            'Estimated profit',
        ]];
        foreach ($data['varieties'] as $variety) {
            $productRows[] = [
                $variety->product_name,
                $variety->brand,
                (float) $variety->quantity,
                (float) $variety->sales,
                (float) $variety->cost,
                (float) $variety->profit,
            ];
        }
        $this->writeWorksheet($products, $productRows);

        $transactions = $spreadsheet->createSheet();
        $transactions->setTitle('Transactions');
        $transactionRows = [[
            'Order number',
            'Date completed',
            'Customer',
            'Payment method',
            'Payment status',
            'GCash reference',
            'Total',
        ]];
        foreach ($data['transactions'] as $transaction) {
            $payment = $transaction->payments->first(
                fn ($payment) => strtolower(trim((string) $payment->method)) === 'gcash'
            );
            $transactionRows[] = [
                $transaction->order_number,
                $transaction->completed_at?->toDateTimeString(),
                $transaction->recipient_name ?: 'N/A',
                $transaction->payment_method ?: 'N/A',
                $transaction->payment_status ?: 'Unknown',
                $payment?->reference_number ?: '',
                (float) $transaction->total,
            ];
        }
        $this->writeWorksheet($transactions, $transactionRows);

        $dailySales = $spreadsheet->createSheet();
        $dailySales->setTitle('Daily Sales');
        $dailyRows = [['Date', 'Completed orders', 'Total sales']];
        foreach ($data['dailySales'] as $day) {
            $dailyRows[] = [
                $day->sales_date,
                (int) $day->order_count,
                (float) $day->sales_total,
            ];
        }
        $this->writeWorksheet($dailySales, $dailyRows);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'hk-report-');
        if ($temporaryFile === false) {
            throw new \RuntimeException('Unable to create a temporary Excel report file.');
        }

        try {
            (new Xlsx($spreadsheet))->save($temporaryFile);
        } catch (\Throwable $exception) {
            @unlink($temporaryFile);
            throw $exception;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        return response()->download($temporaryFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function writeWorksheet(Worksheet $worksheet, array $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $cell = $worksheet->getCell([$columnIndex + 1, $rowIndex + 1]);

                if (is_int($value) || is_float($value)) {
                    $cell->setValueExplicit(
                        $value,
                        DataType::TYPE_NUMERIC
                    );
                } else {
                    $text = (string) ($value ?? '');
                    if (preg_match('/^[=+\-@]/', $text)) {
                        $text = "'".$text;
                    }
                    $cell->setValueExplicit(
                        $text,
                        DataType::TYPE_STRING
                    );
                }
            }
        }

        $lastColumn = Coordinate::stringFromColumnIndex(
            max(1, count($rows[0] ?? []))
        );
        $worksheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        foreach (range(1, max(1, count($rows[0] ?? []))) as $columnIndex) {
            $worksheet->getColumnDimension(
                Coordinate::stringFromColumnIndex($columnIndex)
            )->setAutoSize(true);
        }
    }

    public function exportPdf(Request $request)
    {
        $data = $this->exportData($request);
        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
        ]);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('admin.reports.pdf', $data)->render(), 'UTF-8');
        $pdf->setPaper('a4', 'landscape');
        $pdf->render();

        return response()->streamDownload(
            static function () use ($pdf): void {
                echo $pdf->output();
            },
            'sales-profit-'.$data['startDate'].'-to-'.$data['endDate'].'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
