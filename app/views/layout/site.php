<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> · Water Bill Management System</title><link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(APP_ROOT . '/public/assets/css/style.css') ?>"></head>
<body class="site"><?php require __DIR__ . '/_icons.php'; require __DIR__ . '/_pubnav.php'; ?>
<main><?php require $content; ?></main>
<footer class="sfoot"><div><?= logo() ?><b><?= e(site('site_name')) ?></b></div><div><b>Quick Links</b><br><a href="<?= url('home/index') ?>">Home</a> <a href="<?= url('home/about') ?>">About</a> <a href="<?= url('home/features') ?>">Features</a> <a href="<?= url('home/contact') ?>">Contact</a></div>
<div><?= icon('shield') ?> <b>Secure &amp; Reliable Service</b><br><small>Your data is protected.</small></div></footer>
<div class="copy">© <?= date('Y') ?> <?= e(site('site_name')) ?>. All rights reserved.</div><script src="assets/js/app.js"></script></body></html>
