<?php
class ReportController extends Controller {
    function index() {
        $this->need('admin', 'staff'); $rows = Bill::monthly(); $t = ['billed' => 0, 'collected' => 0, 'outstanding' => 0];
        foreach ($rows as $r) foreach ($t as $k => $_) $t[$k] += (float)$r[$k];
        $this->view('reports/index', ['title' => 'Reports', 'rows' => $rows, 't' => $t]);
    }
    function activity() {
        $this->need('admin'); $a = trim($_GET['action'] ?? '');
        $rows = Model::q('SELECT a.*,u.username FROM audit_logs a LEFT JOIN users u ON u.user_id=a.user_id' . ($a !== '' ? ' WHERE a.action=?' : '') . ' ORDER BY a.log_id DESC LIMIT 200', $a !== '' ? [$a] : [])->fetchAll();
        $this->view('reports/activity', ['title' => 'Activity Log', 'rows' => $rows, 'a' => $a, 'actions' => Model::q('SELECT DISTINCT action FROM audit_logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN)]);
    }
}
