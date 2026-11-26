<?php // Admin settings: branding/contact (logo upload) and billing rules
class SettingController extends Controller {
    function index() { $this->need('admin'); $this->view('settings/index', ['title' => 'Settings', 's' => Setting::all(), 'bs' => Bill::settings(), 'errors' => []]); }
    function branding() {
        $this->need('admin'); $this->post(); $e = []; $d = [];
        foreach (['site_name' => [3, 80], 'tagline' => [0, 120], 'email' => [0, 120], 'phone' => [0, 30], 'address' => [0, 160], 'hours' => [0, 60]] as $k => [$mn, $mx]) {
            $d[$k] = trim($_POST[$k] ?? ''); $l = mb_strlen($d[$k]);
            if ($l < $mn || $l > $mx) $e[] = ucfirst(str_replace('_', ' ', $k)) . ' must be ' . ($mn ? "$mn-$mx" : "up to $mx") . ' characters.';
        }
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $e[] = 'Enter a valid contact email.';
        $cur = Setting::all()['logo']; $logo = $cur;
        if (!empty($_POST['remove_logo'])) $logo = '';
        if (!empty($_FILES['logo']['name'])) {
            $f = $_FILES['logo']; $g = ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 1048576) ? @getimagesize($f['tmp_name']) : false;
            $ext = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'][$g['mime'] ?? ''] ?? null;
            if (!$ext) $e[] = 'Logo must be a PNG, JPG or WebP image under 1 MB.';
            else {
                $dir = APP_ROOT . '/public/uploads'; if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $name = 'logo_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], "$dir/$name")) $logo = $name; else $e[] = 'Could not save the logo (check folder permissions).';
            }
        }
        if ($e) { $this->view('settings/index', ['title' => 'Settings', 's' => $d + Setting::all(), 'bs' => Bill::settings(), 'errors' => $e]); return; }
        if ($logo !== $cur && $cur !== '') @unlink(APP_ROOT . '/public/uploads/' . basename($cur));
        Setting::save($d + ['logo' => $logo]); audit('SETTINGS_UPDATED', 'settings', null, 'branding/contact'); flash('Settings saved.'); $this->redirect('settings/index');
    }
    function billing() {
        $u = $this->need('admin'); $this->post();
        $v = ['unit_rate' => $_POST['unit_rate'] ?? '', 'fixed_charge' => $_POST['fixed_charge'] ?? '', 'tax_rate' => $_POST['tax_rate'] ?? '', 'due_days' => $_POST['due_days'] ?? ''];
        $ok = is_numeric($v['unit_rate']) && $v['unit_rate'] > 0 && $v['unit_rate'] <= 1e9 && is_numeric($v['fixed_charge']) && $v['fixed_charge'] >= 0 && $v['fixed_charge'] <= 1e9
            && is_numeric($v['tax_rate']) && $v['tax_rate'] >= 0 && $v['tax_rate'] <= 100 && ctype_digit((string)$v['due_days']) && $v['due_days'] >= 1 && $v['due_days'] <= 90;
        if (!$ok) { flash('Check the billing values: rate > 0, fixed charge >= 0, tax 0-100%, due days 1-90.', 'error'); $this->redirect('settings/index'); }
        Model::q('INSERT INTO billing_settings (unit_rate,fixed_charge,tax_rate,due_days,currency,updated_by) VALUES (?,?,?,?,?,?)', [$v['unit_rate'], $v['fixed_charge'], $v['tax_rate'], (int)$v['due_days'], 'RWF', $u['id']]);
        audit('BILLING_SETTINGS_UPDATED', 'settings', null, "rate {$v['unit_rate']} fixed {$v['fixed_charge']} tax {$v['tax_rate']}%"); flash('Billing rules saved. They apply to bills generated from now on.'); $this->redirect('settings/index');
    }
}
