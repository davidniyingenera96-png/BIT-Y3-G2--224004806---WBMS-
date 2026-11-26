<?php
class DashboardController extends Controller {
    function index() {
        $u = $this->need('admin', 'staff', 'customer'); $d = ['title' => ucfirst($u['role']) . ' Dashboard'];
        $S = fn(string $q, array $p = []) => (int)Model::scalar($q, $p);
        if ($u['role'] === 'admin') {
            $d += ['customers' => $S('SELECT COUNT(*) FROM customers'), 'bills' => $S('SELECT COUNT(*) FROM bills'), 'payN' => $S("SELECT COUNT(*) FROM payments WHERE status='Completed'"),
                'readN' => $S('SELECT COUNT(*) FROM meter_readings'), 'paidN' => $S("SELECT COUNT(*) FROM bills WHERE status='Paid'"), 'openN' => $S("SELECT COUNT(*) FROM bills WHERE status IN ('Unpaid','Partially Paid')"),
                'overdue' => $S("SELECT COUNT(*) FROM bills WHERE status IN ('Unpaid','Partially Paid') AND due_date<CURDATE()"), 'pendingPay' => Bill::pendingCount(),
                'rev' => array_reverse(Model::q("SELECT DATE_FORMAT(payment_date,'%Y-%m') m, SUM(amount_paid) t FROM payments WHERE status='Completed' GROUP BY m ORDER BY m DESC LIMIT 6")->fetchAll()),
                'acts' => Model::q('SELECT action,details,created_at FROM audit_logs ORDER BY log_id DESC LIMIT 5')->fetchAll()];
        } elseif ($u['role'] === 'staff') {
            $tot = $S("SELECT COUNT(*) FROM bills WHERE status<>'Cancelled'"); $paid = $S("SELECT COUNT(*) FROM bills WHERE status='Paid'");
            $d += ['readings' => Reading::recent(5), 'pct' => $tot ? (int)round($paid / $tot * 100) : 0, 'tot' => $tot, 'pendingPay' => Bill::pendingCount()];
        } else {
            $c = Customer::byUser($u['id']); $d += ['cust' => $c];
            if ($c) $d += ['bills' => Bill::list((int)$c['customer_id']), 'last' => Reading::last((int)$c['customer_id']), 'pays' => Bill::paymentsAll((int)$c['customer_id'])];
        }
        $this->view('dashboard/' . $u['role'], $d);
    }
}
