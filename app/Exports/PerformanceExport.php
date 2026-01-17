<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'تقرير الأداء';
    }

    public function array(): array
    {
        $rows = [];

        // Title
        $rows[] = ['تقرير الأداء (Performance Report)'];
        $rows[] = ['تاريخ التقرير: ' . now()->format('Y-m-d H:i')];
        $rows[] = []; // Empty row

        // Overview Section
        $rows[] = ['[ نظرة عامة - Overview ]'];
        $rows[] = ['إجمالي المبيعات', 'إجمالي الطلبات', 'إجمالي العملاء', 'إجمالي المنتجات'];
        $rows[] = [
            number_format($this->data['overview']['total_revenue'] ?? 0, 2),
            $this->data['overview']['total_orders'] ?? 0,
            $this->data['overview']['total_customers'] ?? 0,
            $this->data['overview']['total_products'] ?? 0,
        ];
        $rows[] = []; // Empty row

        // Orders By Status Section
        $rows[] = ['[ الطلبات حسب الحالة - Orders By Status ]'];
        $rows[] = ['الحالة', 'العدد'];
        foreach ($this->data['charts']['orders_by_status'] ?? [] as $status => $count) {
            $rows[] = [$status, $count];
        }
        $rows[] = []; // Empty row

        // Top Products Section
        $rows[] = ['[ المنتجات الأكثر مبيعاً - Top Selling Products ]'];
        $rows[] = ['اسم المنتج', 'الكمية المباعة', 'إجمالي المبيعات'];
        foreach ($this->data['lists']['top_products'] ?? [] as $product) {
            $rows[] = [
                $product->name ?? 'N/A',
                $product->total_sold ?? 0,
                number_format($product->total_revenue ?? 0, 2),
            ];
        }
        $rows[] = []; // Empty row

        // Recent Orders Section
        $rows[] = ['[ أحدث الطلبات - Recent Orders ]'];
        $rows[] = ['رقم الطلب', 'اسم العميل', 'القيمة', 'الحالة', 'التاريخ'];
        foreach ($this->data['lists']['recent_orders'] ?? [] as $order) {
            $rows[] = [
                $order['order_code'],
                $order['customer_name'],
                number_format($order['total'], 2),
                $order['status'],
                $order['created_at'],
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        // Style section headers (rows starting with "[")
        $highestRow = $sheet->getHighestRow();
        for ($i = 1; $i <= $highestRow; $i++) {
            $cellValue = $sheet->getCell('A' . $i)->getValue();
            if (is_string($cellValue) && str_starts_with($cellValue, '[')) {
                $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setSize(12);
                // Style the header row below section title
                if ($i + 1 <= $highestRow) {
                    $sheet->getStyle('A' . ($i + 1) . ':E' . ($i + 1))->getFont()->setBold(true);
                }
            }
        }

        return [];
    }
}
