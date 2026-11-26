<?php
class PaymentController extends Controller {
    function index() {
        $this->need('admin', 'staff');
        $f = ['q' => trim($_GET['q'] ?? ''), 'method' => $_GET['method'] ?? '', 'status' => $_GET['status'] ?? '', 'from' => $_GET['from'] ?? '', 'to' => $_GET['to'] ?? ''];
        $this->view('payments/index', ['title' => 'Payment Records', 'rows' => Bill::paymentList($f), 'f' => $f]);
    }
    function mine() { $u = $this->need('customer'); $c = Customer::byUser($u['id']); $this->view('payments/mine', ['title' => 'My Payments', 'rows' => $c ? Bill::paymentsAll((int)$c['customer_id']) : []]); }
    function submit() {
        $u = $this->need('customer'); $this->post(); $c = Customer::byUser($u['id']); $id = (int)($_POST['bill_id'] ?? 0);
        $e = $c ? Bill::submit($id, (int)$c['customer_id'], trim($_POST['amount'] ?? ''), $_POST['method'] ?? '', trim($_POST['reference'] ?? ''), $u['id']) : ['No customer record is linked to your account.'];
        flash($e ? implode(' ', $e) : 'Payment submitted. Our staff will confirm it shortly.', $e ? 'error' : 'success'); $this->redirect('bills/show', ['id' => $id]);
    }
    function edit() {
        $this->need('admin'); $p = Bill::findPayment((int)($_GET['id'] ?? 0)); if (!$p) { http_response_code(404); exit('Not found'); }
        $this->view('payments/edit', ['title' => 'Edit Payment', 'p' => $p, 'errors' => []]);
    }
    function update() {
        $this->need('admin'); $this->post(); $id = (int)($_POST['id'] ?? 0);
        $e = Bill::editPayment($id, trim($_POST['payment_date'] ?? ''), $_POST['method'] ?? '', trim($_POST['reference'] ?? ''));
        if (!$e) { flash('Payment updated.'); $this->redirect('payments/index'); }
        $p = Bill::findPayment($id); if (!$p) { http_response_code(404); exit('Not found'); }
        $this->view('payments/edit', ['title' => 'Edit Payment', 'p' => $p, 'errors' => [$e]]);
    }
    function void() { $this->need('admin'); $this->post(); flash(Bill::voidPayment((int)($_POST['payment_id'] ?? 0)) ? 'Payment voided.' : 'Payment could not be voided.'); $this->redirect('payments/index'); }
    function confirm() { $this->need('admin', 'staff'); $this->post(); $e = Bill::confirm((int)($_POST['payment_id'] ?? 0)); flash($e ?? 'Payment confirmed.', $e ? 'error' : 'success'); $this->redirect('payments/index'); }
    function reject() { $this->need('admin', 'staff'); $this->post(); flash(Bill::reject((int)($_POST['payment_id'] ?? 0)) ? 'Payment rejected.' : 'Payment could not be rejected.'); $this->redirect('payments/index'); }
}
