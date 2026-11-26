<div class="pagehead"><div class="secin"><h1>Features</h1><p>Everything you need, from meter to payment.</p></div></div>
<section class="sec alt" id="features"><div class="secin"><div class="fgrid">
<?php foreach ([['gauge', 'Meter Readings', 'Record dated, cumulative readings with the reader identified, and see the previous value before you save.'], ['file', 'Automatic Billing', 'Usage, water charge, fixed charge and tax are calculated from the approved rates, with one bill per customer per month.'],
['card', 'Payment Tracking', 'Record full or partial payments; balances and Paid / Partially Paid / Unpaid status update automatically.'], ['users', 'Customer Portal', 'Customers log in to see only their own meter, bills and payment history.'],
['chart', 'Live Search & Reports', 'Find customers by name or meter number instantly, and follow monthly revenue on the dashboard.'], ['shield', 'Secure by Design', 'CSRF protection, prepared statements, role checks on every page, session timeout and an activity log.']] as [$i, $t, $d]): ?>
<div class="fcard2"><span class="fic"><?= icon($i) ?></span><b><?= e($t) ?></b><p><?= e($d) ?></p></div><?php endforeach; ?></div>
<h3 class="steps-h">How it works</h3><ol class="steps"><li><b>Register</b><span>Staff add the customer and meter.</span></li><li><b>Read</b><span>A meter reading is recorded.</span></li><li><b>Bill</b><span>The bill is generated automatically.</span></li><li><b>Pay &amp; track</b><span>Payments are recorded and visible online.</span></li></ol></div></section>

<section class="cta"><h3>See it in action</h3><a class="btn sm" href="<?= url('auth/login') ?>">Login</a> <a class="btn sm ghost" href="<?= url('home/contact') ?>">Contact us</a></section>
