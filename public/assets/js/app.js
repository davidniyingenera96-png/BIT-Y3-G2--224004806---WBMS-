// No inline scripts are used (strict CSP). Sidebar, password toggle, validation, confirm, live search.
document.getElementById('menuBtn')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('open'));
document.querySelectorAll('.pw button').forEach(b => b.addEventListener('click', () => { const i = b.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password'; }));
document.querySelectorAll('form[data-validate]').forEach(f => f.addEventListener('submit', ev => { if (!f.checkValidity()) { ev.preventDefault(); f.reportValidity(); } }));
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', ev => { if (!confirm(f.dataset.confirm)) ev.preventDefault(); }));
const box = document.getElementById('liveSearch'), body = document.getElementById('custBody');
if (box && body) {
  const original = body.innerHTML; let t;
  const row = c => { const tr = document.createElement('tr');
    [c.full_name, c.meter_no, c.address, ''].forEach(v => { const td = document.createElement('td'); td.textContent = v; tr.appendChild(td); });
    const td = document.createElement('td'); td.className = 'acts2'; [['show', 'view', 'eye', 'View'], ['edit', 'edit', 'edit', 'Edit']].forEach(([act, cls, ic, label]) => { const a = document.createElement('a'); a.className = 'act ' + cls; a.href = 'index.php?r=customers/' + act + '&id=' + encodeURIComponent(c.customer_id); a.innerHTML = '<svg class="ico" aria-hidden="true"><use href="#i-' + ic + '"/></svg>' + label; td.appendChild(a); }); tr.appendChild(td); return tr; };
  const msg = (text, err) => { const tr = document.createElement('tr'), td = document.createElement('td'); td.colSpan = 5; td.textContent = text; td.className = err ? 'err' : 'muted'; if (err) td.style.color = '#DC3545'; tr.appendChild(td); body.replaceChildren(tr); };
  box.addEventListener('input', () => { clearTimeout(t); t = setTimeout(async () => {
    const q = box.value.trim(); if (q.length < 2) { body.innerHTML = original; return; }
    msg('Searching…');
    try { const r = await fetch('index.php?r=api/search&q=' + encodeURIComponent(q), {headers: {Accept: 'application/json'}}); const j = await r.json();
      if (!r.ok || !j.success) throw new Error(j.error || 'Search failed');
      j.data.length ? body.replaceChildren(...j.data.map(row)) : msg('No customers found');
    } catch (e) { msg(e.message, true); } }, 300); });
}
// Role selector on the login card (optional; server verifies the account really has that role)
document.querySelectorAll('.rolebtn').forEach(b => b.addEventListener('click', () => {
  const h = document.getElementById('roleInput'), on = b.classList.contains('on');
  document.querySelectorAll('.rolebtn').forEach(x => x.classList.remove('on'));
  if (!on) { b.classList.add('on'); h.value = b.dataset.role; } else h.value = '';
}));
// Register page: role selector (staff/admin accounts are created by an administrator, so only Customer can submit)
const rr = document.querySelectorAll('.regrole');
if (rr.length) {
  const note = document.getElementById('roleNote'), sub = document.getElementById('regSubmit'), hid = document.getElementById('regRole');
  rr.forEach(b => b.addEventListener('click', () => {
    rr.forEach(x => x.classList.remove('on')); b.classList.add('on'); hid.value = b.dataset.role;
    const cust = b.dataset.role === 'customer'; note.hidden = cust; sub.disabled = !cust;
  }));
}
