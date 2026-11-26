<?php // Admin: manage staff/admin/customer login accounts (deactivate, never delete)
class UserController extends Controller {
    function index() { $this->need('admin'); $r = $_GET['role'] ?? ''; $q = trim($_GET['q'] ?? ''); $this->view('users/index', ['title' => 'Manage Users', 'rows' => User::all($r, $q), 'role' => $r, 'q' => $q]); }
    function create() { $this->need('admin'); $this->form([], []); }
    function edit() { $this->need('admin'); $x = User::find((int)($_GET['id'] ?? 0)); if (!$x) { http_response_code(404); exit('Not found'); } $this->form($x, []); }
    function save() {
        $me = $this->need('admin'); $this->post(); $id = (int)($_POST['id'] ?? 0) ?: null; [$e, $d] = User::validate($_POST, $id);
        if ($id === $me['id'] && $d['role'] !== 'admin') $e[] = 'You cannot remove your own admin role.';
        if ($e) { $this->form(($id ? ['user_id' => $id] : []) + $d, $e); return; }
        if ($id) { User::update($id, $d); audit('USER_UPDATED', 'user', $id); flash('Account updated.'); } else { $id = User::create($d); audit('USER_CREATED', 'user', $id, $d['role']); flash('Account created.'); }
        $this->redirect('users/index');
    }
    function setactive() {
        $me = $this->need('admin'); $this->post(); $id = (int)($_POST['id'] ?? 0); $a = !empty($_POST['active']) ? 1 : 0;
        if ($id === $me['id']) flash('You cannot deactivate your own account.', 'error'); else { User::setActive($id, $a); audit($a ? 'USER_ACTIVATED' : 'USER_DEACTIVATED', 'user', $id); flash('Account updated.'); }
        $this->redirect('users/index');
    }
    function delete() {
        $me = $this->need('admin'); $this->post(); $id = (int)($_POST['id'] ?? 0);
        $e = $id === $me['id'] ? 'You cannot delete your own account.' : User::deleteCustomerAccount($id);
        flash($e ?? 'Customer login deleted. The customer profile and history were kept.', $e ? 'error' : 'success');
        $this->redirect('users/index');
    }
    private function form(array $x, array $errors) { $this->view('users/form', ['title' => !empty($x['user_id']) ? 'Edit Account' : 'New Account', 'x' => $x, 'errors' => $errors]); }
}
