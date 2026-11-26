<?php
// PDO connection. Override credentials in app/config/database.local.php (git-ignored):
// <?php return ['host'=>'127.0.0.1','name'=>'water_bill_management','user'=>'root','pass'=>'yourpass'];
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $f = __DIR__ . '/database.local.php';
    $c = is_file($f) ? require $f : ['host'=>'127.0.0.1','name'=>'water_bill_management','user'=>'root','pass'=>''];
    try {
        $pdo = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4", $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit('Database connection failed. Check app/config/database.php');
    }
    return $pdo;
}
