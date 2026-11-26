<?php
class ReadingController extends Controller {
    private function listData(array $extra = []): array {
        $f = ['cid' => (int)($_GET['cid'] ?? 0), 'from' => (string)($_GET['from'] ?? ''), 'to' => (string)($_GET['to'] ?? '')];
        return $extra + ['title' => 'Meter Readings', 'cust' => Reading::customersWithLast(), 'errors' => [], 'f' => $f, 'rows' => Reading::recent(200, $f['cid'] ?: null, $f['from'], $f['to'])];
    }
    function index() { $this->need('admin', 'staff'); $this->view('readings/index', $this->listData()); }
    function mine() {
        $u = $this->need('customer'); $c = Customer::byUser($u['id']);
        $this->view('readings/mine', ['title' => 'Meter Info', 'c' => $c, 'rows' => $c ? Reading::forCustomer((int)$c['customer_id']) : []]);
    }
    function save() {
        $u = $this->need('admin', 'staff'); $this->post();
        $e = Reading::record((int)($_POST['customer_id'] ?? 0), trim($_POST['reading_date'] ?? ''), trim($_POST['meter_value'] ?? ''), $u['id']);
        if (!$e) { flash('Reading recorded.'); $this->redirect('readings/index'); }
        $this->view('readings/index', $this->listData(['errors' => $e]));
    }
    function edit() {
        $this->need('admin', 'staff'); $r = Reading::find((int)($_GET['id'] ?? 0)); if (!$r) { http_response_code(404); exit('Not found'); }
        $this->view('readings/edit', ['title' => 'Correct Reading', 'r' => $r, 'ok' => Reading::editable($r), 'errors' => []]);
    }
    function update() {
        $this->need('admin', 'staff'); $this->post(); $id = (int)($_POST['id'] ?? 0);
        $e = Reading::update($id, trim($_POST['reading_date'] ?? ''), trim($_POST['meter_value'] ?? ''));
        if (!$e) { flash('Reading corrected.'); $this->redirect('readings/index'); }
        $r = Reading::find($id); if (!$r) { http_response_code(404); exit('Not found'); }
        $this->view('readings/edit', ['title' => 'Correct Reading', 'r' => $r, 'ok' => Reading::editable($r), 'errors' => $e]);
    }
    function delete() { $this->need('admin', 'staff'); $this->post(); $e = Reading::delete((int)($_POST['id'] ?? 0)); flash($e ?? 'Reading deleted.', $e ? 'error' : 'success'); $this->redirect('readings/index'); }
}
