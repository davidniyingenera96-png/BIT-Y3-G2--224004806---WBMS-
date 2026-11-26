<?php
$menus = ['admin' => [['dashboard', 'Dashboard', 'home'], ['users', 'Manage Users', 'users'], ['customers', 'Manage Customers', 'users'], ['readings', 'Meter Readings', 'gauge'], ['bills', 'Bills', 'file'], ['payments', 'Payments', 'card'], ['reports', 'Reports', 'chart'], ['messages', 'Messages', 'mail'], ['settings', 'Settings', 'wrench'], ['profile', 'My Profile', 'user']],
  'staff' => [['dashboard', 'Dashboard', 'home'], ['customers', 'Customers', 'users'], ['readings', 'Record Reading', 'gauge'], ['bills', 'Generate Bills', 'file'], ['payments', 'Payment Records', 'card'], ['reports', 'Reports', 'chart'], ['profile', 'My Profile', 'user']],
  'customer' => [['dashboard', 'Dashboard', 'home'], ['bills', 'My Bills', 'file'], ['payments/mine', 'My Payments', 'card'], ['readings/mine', 'Meter Info', 'gauge'], ['profile', 'My Profile', 'user']]];
$cur = (string)($_GET['r'] ?? 'dashboard/index'); [$n1, $n2] = name_lines();
$on = fn(string $r) => $cur === $r || $cur === $r . '/index' || str_starts_with($cur, $r . '/');
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · <?= e(site('site_name')) ?></title><link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(APP_ROOT . '/public/assets/css/style.css') ?>"></head><body>
<?php require __DIR__ . '/_icons.php'; ?>
<aside class="sidebar" id="sidebar"><div class="brand"><?= logo() ?><div><b><?= e($n1) ?></b><?php if ($n2): ?><small><?= e($n2) ?></small><?php endif; ?></div></div>
<nav><?php foreach ($menus[$u['role']] as [$r, $l, $i]): ?><a href="<?= url($r) ?>" class="<?= $on($r) ? 'active' : '' ?>"><?= icon($i) ?><?= e($l) ?></a><?php endforeach; ?>
<a href="<?= url('home/index') ?>"><?= icon('drop') ?>Website</a>
<form method="post" action="<?= url('auth/logout') ?>"><?= csrf_field() ?><button type="submit" class="navbtn"><?= icon('logout') ?>Logout</button></form></nav></aside>
<div class="main"><header class="topbar"><button class="menu-btn" id="menuBtn" aria-label="Open menu">☰</button><div class="who"><span class="avatar"><?= e(strtoupper($u['username'][0])) ?></span><?= e(ucfirst($u['role'])) ?> · <?= e($u['username']) ?></div></header>
<main class="content"><?php if ($__f = flash()): ?><div class="alert <?= e($__f[1]) ?>" role="alert"><?= e($__f[0]) ?></div><?php endif; require $content; ?></main></div><script src="assets/js/app.js"></script></body></html>
