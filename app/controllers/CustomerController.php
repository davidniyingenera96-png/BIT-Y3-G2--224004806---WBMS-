<?php
class CustomerController extends Controller {
    function index() {
        $this->need('admin', 'staff'); $in = ($_GET['show'] ?? '') === 'inactive';
        $this->view('customers/index', ['title' => 'Customers', 'inactive' => $in, 'rows' => Customer::all(trim($_GET['q'] ?? ''), $in ? 0 : 1)]);
    }
    function activate() { $this->need('admin', 'staff'); $this->post(); $id = (int)($_POST['id'] ?? 0); Customer::activate($id); audit('CUSTOMER_ACTIVATED', 'customer', $id); flash('Customer reactivated.'); $this->redirect('customers/index'); }
    function delete() { $this->need('admin'); $this->post(); $e = Customer::delete((int)($_POST['id'] ?? 0)); flash($e ?? 'Customer deleted.', $e ? 'error' : 'success'); $this->redirect('customers/index', ['show' => 'inactive']); }
    function show() {
        $this->need('admin', 'staff'); $c = Customer::find((int)($_GET['id'] ?? 0)); if (!$c) { http_response_code(404); exit('Not found'); }
        $this->view('customers/show', ['title' => $c['full_name'], 'c' => $c, 'readings' => Reading::forCustomer((int)$c['customer_id']), 'bills' => Bill::list((int)$c['customer_id'])]);
    }
    function create() { $this->need('admin', 'staff'); $this->form(null, []); }
    function edit() { $this->need('admin', 'staff'); $c = Customer::find((int)($_GET['id'] ?? 0)); if (!$c) { http_response_code(404); exit('Not found'); } $this->form($c, []); }
    function save() {
        $u = $this->need('admin', 'staff'); $this->post(); $id = (int)($_POST['id'] ?? 0) ?: null;
        [$e, $d] = Customer::validate($_POST, $id);
        if ($e) { $this->form(($id ? ['customer_id' => $id] : []) + $d, $e); return; }
        if ($id) { Customer::update($id, $d); audit('CUSTOMER_UPDATED', 'customer', $id); flash('Customer updated.'); }
        else { $id = Customer::create($d); audit('CUSTOMER_CREATED', 'customer', $id, $d['meter_no']); flash('Customer registered.'); }
        $this->redirect('customers/index');
    }
    function deactivate() { $this->need('admin', 'staff'); $this->post(); $id = (int)($_POST['id'] ?? 0); Customer::deactivate($id); audit('CUSTOMER_DEACTIVATED', 'customer', $id); flash('Customer deactivated (history kept).'); $this->redirect('customers/index'); }
    private function form(?array $c, array $errors) { $this->view('customers/form', ['title' => ($c && isset($c['customer_id']) ? 'Edit' : 'Register') . ' Customer', 'c' => $c ?? [], 'errors' => $errors]); }
}
