<?php
class BillController extends Controller {
    function index() {
        $u = $this->need('admin', 'staff', 'customer'); $own = $u['role'] === 'customer'; $c = $own ? Customer::byUser($u['id']) : null;
        $this->view('bills/index', ['title' => $own ? 'My Bills' : 'Bills', 'own' => $own, 'rows' => $own ? ($c ? Bill::list((int)$c['customer_id']) : []) : Bill::list(null, $_GET['status'] ?? ''),
            'cust' => $own ? [] : Reading::customersWithLast(), 'status' => $_GET['status'] ?? '']);
    }
    function generate() {
        $u = $this->need('admin', 'staff'); $this->post();
        [$e, $id] = Bill::generate((int)($_POST['customer_id'] ?? 0), trim($_POST['month'] ?? ''), $u['id']);
        if ($e) flash(implode(' ', $e), 'error'); else flash('Bill generated.');
        $this->redirect($id ? 'bills/show' : 'bills/index', $id ? ['id' => $id] : []);
    }
    function show() {
        $u = $this->need('admin', 'staff', 'customer'); $b = Bill::find((int)($_GET['id'] ?? 0));
        // Ownership enforced server-side: customers only see bills of their own linked record
        if (!$b || ($u['role'] === 'customer' && (int)$b['user_id'] !== $u['id'])) { http_response_code(404); $this->view('errors/page', ['title' => 'Not found', 'msg' => 'Bill not found.']); return; }
        $this->view('bills/show', ['title' => 'Bill ' . $b['billing_month'], 'b' => $b, 'pays' => Bill::payments((int)$b['bill_id']), 'role' => $u['role']]);
    }
    function pay() {
        $u = $this->need('admin', 'staff'); $this->post(); $id = (int)($_POST['bill_id'] ?? 0);
        $e = Bill::pay($id, trim($_POST['amount'] ?? ''), trim($_POST['payment_date'] ?? ''), $_POST['method'] ?? '', trim($_POST['reference'] ?? ''), $u['id']);
        flash($e ? implode(' ', $e) : 'Payment recorded.', $e ? 'error' : 'success'); $this->redirect('bills/show', ['id' => $id]);
    }
    function due() {
        $this->need('admin'); $this->post(); $id = (int)($_POST['bill_id'] ?? 0); $e = Bill::updateDue($id, trim($_POST['due_date'] ?? ''));
        flash($e ?? 'Due date updated.', $e ? 'error' : 'success'); $this->redirect('bills/show', ['id' => $id]);
    }
    function cancel() {
        $this->need('admin'); $this->post(); $id = (int)($_POST['bill_id'] ?? 0); $e = Bill::cancel($id);
        flash($e ?? 'Bill cancelled.', $e ? 'error' : 'success'); $this->redirect('bills/show', ['id' => $id]);
    }
    function voidpay() {
        $this->need('admin'); $this->post(); $bill = (int)($_POST['bill_id'] ?? 0);
        flash(Bill::voidPayment((int)($_POST['payment_id'] ?? 0)) ? 'Payment voided.' : 'Payment could not be voided.', 'success'); $this->redirect('bills/show', ['id' => $bill]);
    }
}
