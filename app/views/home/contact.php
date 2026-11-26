<div class="pagehead"><div class="secin"><h1>Contact Us</h1><p>Questions about your account, a bill or a meter reading? We are here to help.</p></div></div>
<section class="sec" id="contact"><div class="secin">
<?php if ($f = flash()): ?><div class="alert <?= e($f[1]) ?>" role="alert"><?= e($f[0]) ?></div><?php endif; ?>
<div class="contact"><div class="cinfo"><p>Questions about your account, a bill or a meter reading? Send us a message and we will reply as soon as possible.</p>
<ul><li><?= icon('mail') ?><span><b>Email</b><br><?= e($site['email']) ?></span></li><li><?= icon('phone') ?><span><b>Phone</b><br><?= e($site['phone']) ?></span></li><li><?= icon('pin') ?><span><b>Address</b><br><?= e($site['address']) ?></span></li><li><?= icon('clock') ?><span><b>Office hours</b><br><?= e($site['hours']) ?></span></li></ul></div>
<form class="cform" method="post" action="<?= url('home/send') ?>" data-validate><?= csrf_field() ?><div class="hp" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
<div class="two"><div><label for="cn">Full name</label><input id="cn" name="name" required minlength="2" maxlength="80" value="<?= e($old['name'] ?? '') ?>"></div><div><label for="ce">Email</label><input id="ce" type="email" name="email" required maxlength="120" value="<?= e($old['email'] ?? '') ?>"></div></div>
<label for="cs">Subject</label><input id="cs" name="subject" required minlength="3" maxlength="120" value="<?= e($old['subject'] ?? '') ?>">
<label for="cm">Message</label><textarea id="cm" name="message" rows="5" required minlength="10" maxlength="2000"><?= e($old['message'] ?? '') ?></textarea>
<button class="btn"><?= icon('send') ?>Send message</button></form></div></div></section>
