<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/database.php';

class ReportsController extends Controller {
    public function index() {
        $this->requireAuth();
        $this->render('reports/index', [
            'title' => 'Laporan'
        ]);
    }
    
    public function api() {
        $this->requireAuthJson();
        try {
            $today = dbFetch("SELECT COALESCE(SUM(total_amount),0) as total, COUNT(*) as count FROM transactions WHERE DATE(created_at)=CURDATE()");
            $last7 = dbFetchAll("SELECT DATE(created_at) as d, COALESCE(SUM(total_amount),0) as total, COUNT(*) as count FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
            $priceColRow = dbFetch(
                "SELECT COLUMN_NAME 
                 FROM information_schema.columns 
                 WHERE table_schema = :schema 
                   AND table_name = 'transaction_items' 
                   AND COLUMN_NAME IN ('total_price','price','unit_price')
                 ORDER BY FIELD(COLUMN_NAME, 'total_price','price','unit_price')
                 LIMIT 1",
                ['schema' => DB_NAME]
            );
            $priceCol = $priceColRow['COLUMN_NAME'] ?? 'price';
            $revenueExpr = ($priceCol === 'total_price')
                ? "SUM(ti.total_price) as revenue"
                : "SUM(ti.quantity * ti.{$priceCol}) as revenue";
            $topProducts = dbFetchAll("SELECT p.product_name, SUM(ti.quantity) as qty, {$revenueExpr} FROM transaction_items ti JOIN transactions t ON ti.transaction_id=t.id_transaction JOIN products p ON ti.product_id=p.id_product WHERE t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY ti.product_id ORDER BY qty DESC LIMIT 10");
            $colRow = dbFetch(
                "SELECT COLUMN_NAME 
                 FROM information_schema.columns 
                 WHERE table_schema = :schema 
                   AND table_name = 'branch_inventory' 
                   AND COLUMN_NAME IN ('quantity','stock_quantity')
                 ORDER BY FIELD(COLUMN_NAME, 'quantity','stock_quantity')
                 LIMIT 1",
                ['schema' => DB_NAME]
            );
            $stockCol = $colRow['COLUMN_NAME'] ?? 'quantity';
            $lowStockRow = dbFetch("SELECT COUNT(*) as low_count FROM branch_inventory WHERE {$stockCol} <= COALESCE(min_stock, 0)");
            $lowStock = $lowStockRow['low_count'] ?? 0;
            $payloadToday = $today ?: ['total' => 0, 'count' => 0];
            $payloadLast7 = $last7 ?: [];
            $payloadTop = $topProducts ?: [];
            $isEmpty = (floatval($payloadToday['total'] ?? 0) === 0)
                       && (intval($payloadToday['count'] ?? 0) === 0)
                       && (intval($lowStock) === 0)
                       && (count($payloadLast7) === 0)
                       && (count($payloadTop) === 0);
            $this->json([
                'status' => 'success',
                'message' => $isEmpty ? 'Belum ada data laporan' : 'Success',
                'data' => [
                    'today' => $payloadToday,
                    'last7' => $payloadLast7,
                    'topProducts' => $payloadTop,
                    'lowStock' => $lowStock
                ]
            ]);
        } catch (Exception $e) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memuat laporan',
                'data' => [
                    'today' => ['total' => 0, 'count' => 0],
                    'last7' => [],
                    'topProducts' => [],
                    'lowStock' => 0
                ]
            ], 500);
        }
    }
}
