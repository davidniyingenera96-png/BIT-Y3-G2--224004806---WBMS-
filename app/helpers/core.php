<?php
// Bootstrap: autoload, security headers, hardened session, global helpers.
declare(strict_types=1);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/app/config/database.php';
spl_autoload_register(function (string $c) {
    foreach (['core', 'models', 'controllers'] as $d) { $f = APP_ROOT . "/app/$d/$c.php"; if (is_file($f)) { require_once $f; return; } }
});
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'none'; form-action 'self'");
header('X-Frame-Options: DENY'); header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: same-origin');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
// Inactivity timeout: 30 minutes
if (isset($_SESSION['user'])) {
    if (time() - ($_SESSION['last'] ?? 0) > 1800) { $_SESSION = []; session_destroy(); session_start(); $_SESSION['flash'] = ['Session expired. Please log in again.', 'error']; }
    else $_SESSION['last'] = time();
}
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n): string { return 'RWF ' . number_format((float)$n, 0); }
function url(string $r, array $q = []): string { return 'index.php?r=' . $r . ($q ? '&' . http_build_query($q) : ''); }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void { if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) { http_response_code(419); exit('Invalid or expired form token.'); } }
function user(): ?array { return $_SESSION['user'] ?? null; }
function flash(?string $m = null, string $t = 'success'): ?array {
    if ($m !== null) { $_SESSION['flash'] = [$m, $t]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function badge(string $s): string { return '<span class="badge b-' . e(explode(' ', $s)[0]) . '">' . e($s) . '</span>'; }
function audit(string $action, string $entity, ?int $id = null, string $details = ''): void {
    Model::q('INSERT INTO audit_logs (user_id,action,entity,entity_id,details,ip_address) VALUES (?,?,?,?,?,?)',
        [user()['id'] ?? null, $action, $entity, $id, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
}
function icon(string $n): string { return '<svg class="ico" aria-hidden="true"><use href="#i-' . $n . '"/></svg>'; }
function ago(string $ts): string { $d = time() - strtotime($ts); if ($d < 60) return 'just now'; if ($d < 3600) return intdiv($d, 60) . ' min ago'; if ($d < 86400) return intdiv($d, 3600) . ' h ago'; return intdiv($d, 86400) . ' d ago'; }
function short($n): string { $n = (float)$n; return $n >= 1e6 ? round($n / 1e6, 1) . 'M' : ($n >= 1e3 ? round($n / 1e3) . 'K' : (string)round($n)); }
function site(string $k): string { return (string)(Setting::all()[$k] ?? ''); }
function name_lines(): array { $w = preg_split('/\s+/', trim(site('site_name'))); return count($w) > 2 ? [implode(' ', array_slice($w, 0, 2)), implode(' ', array_slice($w, 2))] : [implode(' ', $w), '']; }
function logo(string $cls = ''): string { $l = site('logo'); return ($l !== '' && is_file(APP_ROOT . '/public/uploads/' . $l)) ? '<img class="logo ' . e($cls) . '" src="uploads/' . e($l) . '" alt="Logo">' : '<span class="drop ' . e($cls) . '"></span>'; }
const ACT_ICONS = ['view' => 'eye', 'edit' => 'edit', 'del' => 'trash', 'ok' => 'check', 'warn' => 'x', 'add' => 'plus', 'restore' => 'restore', 'save' => 'save'];
/** Styled CRUD link-button: act_link('edit', 'Edit', url(...)) */
function act_link(string $type, string $label, string $href): string { return '<a class="act ' . e($type) . '" href="' . e($href) . '">' . icon(ACT_ICONS[$type] ?? 'eye') . e($label) . '</a>'; }
/** Styled CRUD POST button with CSRF token and optional confirm prompt: act_form('del', 'Delete', 'customers/delete', ['id' => 5], 'Sure?') */
function act_form(string $type, string $label, string $route, array $hidden, string $confirm = ''): string {
    $h = csrf_field(); foreach ($hidden as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    return '<form method="post" class="inl" action="' . e(url($route)) . '"' . ($confirm !== '' ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . $h . '<button class="act ' . e($type) . '">' . icon(ACT_ICONS[$type] ?? 'eye') . e($label) . '</button></form>';
}
