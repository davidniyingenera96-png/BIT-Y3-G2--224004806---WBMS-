<?php $__r = (string)($_GET['r'] ?? 'home/index'); $__u = user(); [$__a, $__b] = name_lines(); ?>
<header class="pnav"><a class="sbrand" href="<?= url('home/index') ?>"><?= logo() ?><span><b><?= e($__a) ?></b><?php if ($__b): ?><small><?= e($__b) ?></small><?php endif; ?></span></a>
<nav class="slinks"><?php foreach ([['home/index', 'Home', 'home'], ['home/about', 'About', 'users'], ['home/features', 'Features', 'check'], ['home/contact', 'Contact', 'mail']] as [$__p, $__l, $__i]): ?>
<a href="<?= url($__p) ?>" class="<?= ($__r === $__p) ? 'pill' : '' ?>"><?= icon($__i) ?><?= $__l ?></a><?php endforeach; ?></nav>
<div class="sact"><?php if ($__u): ?><a class="btn sm" href="<?= url('dashboard') ?>"><?= icon('home') ?>Dashboard</a><?php else: ?><a class="btn sm ghost" href="<?= url('auth/register') ?>"><?= icon('userplus') ?>Register</a><a class="btn sm" href="<?= url('auth/login') ?>"><?= icon('login') ?>Login</a><?php endif; ?></div></header>
