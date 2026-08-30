<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;

class FinanceService {

    /**
     * Compute comprehensive financial dashboard KPIs with optional date range.
     */
    public static function getDashboardKpis(?string $startDate = null, ?string $endDate = null): array {
        $dateCondition = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateCondition = " AND created_at BETWEEN :start AND :end";
            $params['start'] = $startDate . ' 00:00:00';
            $params['end']   = $endDate . ' 23:59:59';
        }

        // 1. Gross Revenue
        $grossRevenue = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status IN ('successful', 'partially_refunded', 'refunded') {$dateCondition}",
            $params
        ) ?: 0.0);

        // 2. Revenue This Month & Today
        $revenueThisMonth = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status IN ('successful', 'partially_refunded', 'refunded') AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"
        ) ?: 0.0);

        $revenueToday = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status IN ('successful', 'partially_refunded', 'refunded') AND DATE(created_at) = CURDATE()"
        ) ?: 0.0);

        // 3. Payment Counts by Status
        $successfulPayments = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM payments WHERE status IN ('successful', 'partially_refunded', 'refunded') {$dateCondition}",
            $params
        ) ?: 0);

        $pendingPayments = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM payments WHERE status = 'pending' {$dateCondition}",
            $params
        ) ?: 0);

        $failedPayments = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM payments WHERE status IN ('failed', 'cancelled') {$dateCondition}",
            $params
        ) ?: 0);

        // 4. Refunded Amount
        $refundedAmount = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE status IN ('processed', 'approved') {$dateCondition}",
            $params
        ) ?: 0.0);

        // 5. Net Revenue
        $netRevenue = max(0.00, $grossRevenue - $refundedAmount);

        // 6. Outstanding / Pending Amount
        $outstandingAmount = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE payment_status = 'pending' {$dateCondition}",
            $params
        ) ?: 0.0);

        // 7. Orders & Average Order Value (AOV)
        $totalOrders = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM orders WHERE 1=1 {$dateCondition}",
            $params
        ) ?: 0);

        $completedOrdersCount = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM orders WHERE payment_status = 'paid' {$dateCondition}",
            $params
        ) ?: 0);

        $avgOrderValue = $completedOrdersCount > 0 ? ($grossRevenue / $completedOrdersCount) : 0.0;

        // 8. Paid vs Free Enrollments
        $paidEnrollments = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT e.id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.price > 0"
        ) ?: 0);

        $freeEnrollments = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT e.id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.price <= 0"
        ) ?: 0);

        return [
            'gross_revenue'       => $grossRevenue,
            'revenue_this_month'  => $revenueThisMonth,
            'revenue_today'       => $revenueToday,
            'successful_payments' => $successfulPayments,
            'pending_payments'    => $pendingPayments,
            'failed_payments'     => $failedPayments,
            'refunded_amount'     => $refundedAmount,
            'net_revenue'         => $netRevenue,
            'outstanding_amount'  => $outstandingAmount,
            'total_orders'        => $totalOrders,
            'completed_orders'    => $completedOrdersCount,
            'avg_order_value'     => $avgOrderValue,
            'paid_enrollments'    => $paidEnrollments,
            'free_enrollments'    => $freeEnrollments
        ];
    }

    /**
     * Get chart dataset series for Chart.js dashboard charts.
     */
    public static function getChartData(): array {
        // 1. Daily revenue for past 14 days
        $days = [];
        $revenueTrend = [];
        $ordersTrend = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $days[] = date('M d', strtotime($date));

            $rev = (float)(Database::fetchValue(
                "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful' AND DATE(created_at) = :d",
                ['d' => $date]
            ) ?: 0.0);
            $revenueTrend[] = $rev;

            $ordersCount = (int)(Database::fetchValue(
                "SELECT COUNT(*) FROM orders WHERE DATE(created_at) = :d",
                ['d' => $date]
            ) ?: 0);
            $ordersTrend[] = $ordersCount;
        }

        // 2. Revenue breakdown by Course (Top 5)
        $courseBreakdown = Database::fetchAll(
            "SELECT c.title, COALESCE(SUM(oi.total_amount), 0) as total_revenue, COUNT(DISTINCT o.id) as sales_count
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             JOIN courses c ON oi.item_id = c.id
             WHERE oi.item_type = 'course' AND o.payment_status = 'paid'
             GROUP BY c.id
             ORDER BY total_revenue DESC
             LIMIT 5"
        );

        // 3. Revenue breakdown by Category
        $categoryBreakdown = Database::fetchAll(
            "SELECT cat.name as category_name, COALESCE(SUM(oi.total_amount), 0) as total_revenue
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             JOIN courses c ON oi.item_id = c.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             WHERE oi.item_type = 'course' AND o.payment_status = 'paid'
             GROUP BY cat.id
             ORDER BY total_revenue DESC"
        );

        // 4. Revenue breakdown by Payment Method
        $paymentMethods = Database::fetchAll(
            "SELECT payment_method, COUNT(*) as tx_count, COALESCE(SUM(amount), 0) as total_amount
             FROM payments
             WHERE status = 'successful'
             GROUP BY payment_method"
        );

        return [
            'labels'             => $days,
            'revenue_series'     => $revenueTrend,
            'orders_series'      => $ordersTrend,
            'courses'            => $courseBreakdown,
            'categories'         => $categoryBreakdown,
            'payment_methods'    => $paymentMethods
        ];
    }

    /**
     * Filter and paginate Orders.
     */
    public static function getOrders(array $filters = [], int $page = 1, int $perPage = 20): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(o.order_number LIKE :q OR u.name LIKE :q2 OR u.email LIKE :q3 OR o.billing_name LIKE :q4)";
            $params['q'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
            $params['q3'] = '%' . trim($filters['q']) . '%';
            $params['q4'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "o.status = :st";
            $params['st'] = $filters['status'];
        }

        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $conditions[] = "o.payment_status = :pst";
            $params['pst'] = $filters['payment_status'];
        }

        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $conditions[] = "o.payment_method = :pm";
            $params['pm'] = $filters['payment_method'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "o.created_at BETWEEN :start AND :end";
            $params['start'] = $filters['start_date'] . ' 00:00:00';
            $params['end'] = $filters['end_date'] . ' 23:59:59';
        }

        $whereSql = implode(' AND ', $conditions);

        $countSql = "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id WHERE {$whereSql}";
        $total = (int)(Database::fetchValue($countSql, $params) ?: 0);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
                       (SELECT item_title FROM order_items WHERE order_id = o.id LIMIT 1) as first_item_title,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE {$whereSql}
                ORDER BY o.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $orders = Database::fetchAll($sql, $params);

        return [
            'data'         => $orders,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Filter and paginate Payments.
     */
    public static function getPayments(array $filters = [], int $page = 1, int $perPage = 20): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(p.transaction_reference LIKE :q OR o.order_number LIKE :q2 OR u.name LIKE :q3 OR u.email LIKE :q4)";
            $params['q'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
            $params['q3'] = '%' . trim($filters['q']) . '%';
            $params['q4'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "p.status = :st";
            $params['st'] = $filters['status'];
        }

        if (!empty($filters['gateway']) && $filters['gateway'] !== 'all') {
            $conditions[] = "p.gateway = :gw";
            $params['gw'] = $filters['gateway'];
        }

        $whereSql = implode(' AND ', $conditions);
        $total = (int)(Database::fetchValue("SELECT COUNT(*) FROM payments p JOIN orders o ON p.order_id = o.id JOIN users u ON p.user_id = u.id WHERE {$whereSql}", $params) ?: 0);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, o.order_number, u.name as customer_name, u.email as customer_email
                FROM payments p
                JOIN orders o ON p.order_id = o.id
                JOIN users u ON p.user_id = u.id
                WHERE {$whereSql}
                ORDER BY p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $payments = Database::fetchAll($sql, $params);

        return [
            'data'         => $payments,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Get Ledger Transactions.
     */
    public static function getLedgerTransactions(array $filters = [], int $page = 1, int $perPage = 30): array {
        $offset = ($page - 1) * $perPage;
        $total = (int)(Database::fetchValue("SELECT COUNT(*) FROM financial_transactions") ?: 0);

        $sql = "SELECT ft.*, o.order_number, u.name as user_name
                FROM financial_transactions ft
                LEFT JOIN orders o ON ft.order_id = o.id
                LEFT JOIN users u ON ft.user_id = u.id
                ORDER BY ft.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data'         => Database::fetchAll($sql),
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Export Orders to CSV.
     */
    public static function exportOrdersCsv(array $filters = []): string {
        $result = static::getOrders($filters, 1, 5000);
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['Order #', 'Customer Name', 'Customer Email', 'Subtotal', 'Discount', 'Tax', 'Final Total', 'Currency', 'Payment Method', 'Payment Status', 'Order Status', 'Date']);

        foreach ($result['data'] as $o) {
            fputcsv($output, [
                $o['order_number'],
                $o['customer_name'],
                $o['customer_email'],
                $o['subtotal_amount'],
                $o['discount_amount'],
                $o['tax_amount'],
                $o['final_amount'],
                $o['currency'],
                strtoupper($o['payment_method'] ?? 'N/A'),
                strtoupper($o['payment_status']),
                strtoupper($o['status']),
                $o['created_at']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
