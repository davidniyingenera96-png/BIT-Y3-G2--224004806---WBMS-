<?php
class AuthController extends Controller {
    function login() {
        if (user()) $this->redirect('dashboard/index');
        $err = ''; $identity = ''; $want = in_array($_GET['role'] ?? '', ['admin', 'staff', 'customer'], true) ? $_GET['role'] : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $identity = trim($_POST['identity'] ?? '');
            if (($_SESSION['lock'] ?? 0) > time()) $err = 'Too many failed attempts. Try again in a few minutes.';
            else {
                $want = in_array($_POST['role'] ?? '', ['admin', 'staff', 'customer'], true) ? $_POST['role'] : '';
                $pw = (string)($_POST['password'] ?? '');
                $r = $identity !== '' ? User::byLogin($identity) : null;
                // Optional role button: the account must really have that role. Each role lands on its own dashboard.
                if ($r && $r['is_active'] && password_verify($pw, $r['password_hash']) && ($want === '' || $r['role'] === $want)) {
                    session_regenerate_id(true);
                    $_SESSION = ['user' => ['id' => (int)$r['user_id'], 'username' => $r['username'], 'role' => $r['role']], 'last' => time()];
                    audit('LOGIN', 'user', (int)$r['user_id']); $this->redirect('dashboard/index');
                }
                $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
                if ($_SESSION['fails'] >= 5) { $_SESSION['lock'] = time() + 300; $_SESSION['fails'] = 0; }
                $err = 'Invalid credentials, inactive account or wrong role selected.';
            }
        }
        $this->view('auth/login', ['title' => 'Login', 'err' => $err, 'identity' => $identity, 'role' => $want]);
    }
    function register() {
        if (user()) $this->redirect('dashboard/index');
        $errs = []; $v = ['full_name' => '', 'meter_no' => '', 'address' => '', 'phone' => '', 'username' => '', 'email' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
            $v['meter_no'] = strtoupper($v['meter_no']); $pw = (string)($_POST['password'] ?? '');
            if (($_POST['role'] ?? 'customer') !== 'customer') $errs[] = 'Staff and administrator accounts are created by the system administrator.';
            if (mb_strlen($v['full_name']) < 2 || mb_strlen($v['full_name']) > 120) $errs[] = 'Full name must be 2-120 characters.';
            if (!preg_match('/^[A-Z0-9-]{3,30}$/', $v['meter_no'])) $errs[] = 'Meter number: 3-30 letters, digits or hyphens.';
            if ($v['address'] === '' || mb_strlen($v['address']) > 255) $errs[] = 'Address is required (max 255 characters).';
            if ($v['phone'] !== '' && !preg_match('/^\+?[0-9 ]{7,20}$/', $v['phone'])) $errs[] = 'Phone number is invalid.';
            if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $v['username'])) $errs[] = 'Username: 3-50 letters, numbers, . _ -';
            if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || strlen($v['email']) > 120) $errs[] = 'Enter a valid email address.';
            if (!User::strong($pw)) $errs[] = 'Password needs 8+ characters with letters and numbers.';
            if ($pw !== (string)($_POST['confirm'] ?? '')) $errs[] = 'Passwords do not match.';
            if (!$errs) { $errs = User::registerCustomer($v + ['password' => $pw]); if (!$errs) { flash('Account created. You can now log in.'); $this->redirect('auth/login'); } }
        }
        $this->view('auth/register', ['title' => 'Register', 'errs' => $errs, 'v' => $v]);
    }
    function logout() {
        $this->post();
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Strict',
        ]);
        session_destroy();
        $this->redirect('auth/login');
    }
}
