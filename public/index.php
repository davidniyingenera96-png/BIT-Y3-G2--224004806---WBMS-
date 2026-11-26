<?php // Front controller: ?r=controller/action  (whitelisted to existing public controller methods)
require __DIR__ . '/../app/helpers/core.php';
$r = trim((string)($_GET['r'] ?? (user() ? 'dashboard/index' : 'home/index')), '/');
[$c, $a] = array_pad(explode('/', $r, 2), 2, 'index');
// URL aliases: plural menu/route names -> singular controller names
$c = ['customers' => 'customer', 'readings' => 'reading', 'bills' => 'bill', 'messages' => 'message', 'users' => 'user', 'payments' => 'payment', 'reports' => 'report', 'settings' => 'setting'][$c] ?? $c;
try {
    $cls = ucfirst($c) . 'Controller';
    if (!preg_match('/^[a-z]+$/', $c) || !preg_match('/^[a-z]+$/', $a) || !is_file(APP_ROOT . "/app/controllers/$cls.php")
        || !method_exists($cls, $a) || !(new ReflectionMethod($cls, $a))->isPublic()) { http_response_code(404); (new ErrorController)->notfound(); exit; }
    (new $cls)->$a();
} catch (Throwable $t) { error_log((string)$t); http_response_code(500); (new ErrorController)->failure(); }
