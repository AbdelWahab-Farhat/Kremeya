<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; background-color: #ddd; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير الأداء (Performance Report)</h1>
        <p>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <!-- Overview -->
    <div class="section-title">نظرة عامة (Overview)</div>
    <table>
        <thead>
            <tr>
                <th>إجمالي المبيعات</th>
                <th>إجمالي الطلبات</th>
                <th>إجمالي العملاء</th>
                <th>إجمالي المنتجات</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($data['overview']['total_revenue'] ?? 0, 2) }}</td>
                <td>{{ $data['overview']['total_orders'] ?? 0 }}</td>
                <td>{{ $data['overview']['total_customers'] ?? 0 }}</td>
                <td>{{ $data['overview']['total_products'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Orders By Status -->
    <div class="section-title">الطلبات حسب الحالة (Orders By Status)</div>
    <table>
        <thead>
            <tr>
                <th>الحالة</th>
                <th>العدد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['charts']['orders_by_status'] ?? [] as $status => $count)
                <tr>
                    <td>{{ $status }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Top Products -->
    <div class="section-title">المنتجات الأكثر مبيعاً (Top Selling Products)</div>
    <table>
        <thead>
            <tr>
                <th>اسم المنتج</th>
                <th>الكمية المباعة</th>
                <th>إجمالي المبيعات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['lists']['top_products'] ?? [] as $product)
                <tr>
                    <td>{{ $product->name ?? 'N/A' }}</td>
                    <td>{{ $product->total_sold ?? 0 }}</td>
                    <td>{{ number_format($product->total_revenue ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Recent Orders -->
    <div class="section-title">أحدث الطلبات (Recent Orders)</div>
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>اسم العميل</th>
                <th>القيمة</th>
                <th>الحالة</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['lists']['recent_orders'] ?? [] as $order)
                <tr>
                    <td>{{ $order['order_code'] }}</td>
                    <td>{{ $order['customer_name'] }}</td>
                    <td>{{ number_format($order['total'], 2) }}</td>
                    <td>{{ $order['status'] }}</td>
                    <td>{{ $order['created_at'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
