<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> · <?= e(site('site_name')) ?></title><link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(APP_ROOT . '/public/assets/css/style.css') ?>"></head>
<body class="lp"><?php require __DIR__ . '/_icons.php'; require __DIR__ . '/_pubnav.php'; [$n1, $n2] = name_lines(); ?>
<main class="loginscreen" id="top"><div class="lbrand"><div class="lbrow"><h1><?= e($n1) ?><?php if ($n2): ?><br><span><?= e($n2) ?></span><?php endif; ?></h1></div><p class="ltag"><?= e(site('tagline')) ?></p></div>
<div class="authbox"><?php if ($__f = flash()): ?><div class="alert <?= e($__f[1]) ?>" role="alert"><?= e($__f[0]) ?></div><?php endif; require $content; ?></div>
<svg class="lwave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 70C220 130 460 20 760 62s460 50 680-20v78H0z" fill="#1976D2" opacity=".9"/><path d="M0 95C260 60 520 120 800 90s420-30 640 5v25H0z" fill="#0d47a1"/></svg></main>
<script src="assets/js/app.js"></script></body></html>
