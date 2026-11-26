<?php // My Profile for every role (customers can also edit phone and address)
class ProfileController extends Controller {
    function index() { $u = $this->need('admin', 'staff', 'customer'); $this->view('profile/index', ['title' => 'My Profile', 'acct' => User::find($u['id']), 'cust' => $u['role'] === 'customer' ? Customer::byUser($u['id']) : null]); }
    function save() {
        $u = $this->need('admin', 'staff', 'customer'); $this->post(); $e = [];
        if ($m = User::setEmail($u['id'], trim($_POST['email'] ?? ''))) $e[] = $m;
        if ($u['role'] === 'customer' && ($c = Customer::byUser($u['id']))) $e = array_merge($e, Customer::updateOwn((int)$c['customer_id'], trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? '')));
        if (!$e) audit('PROFILE_UPDATED', 'user', $u['id']);
        flash($e ? implode(' ', $e) : 'Profile updated.', $e ? 'error' : 'success'); $this->redirect('profile/index');
    }
    function password() {
        $u = $this->need('admin', 'staff', 'customer'); $this->post();
        $m = User::changePassword($u['id'], (string)($_POST['current'] ?? ''), (string)($_POST['new'] ?? ''), (string)($_POST['confirm'] ?? ''));
        if (!$m) audit('PASSWORD_CHANGED', 'user', $u['id']);
        flash($m ?? 'Password changed.', $m ? 'error' : 'success'); $this->redirect('profile/index');
    }
}
