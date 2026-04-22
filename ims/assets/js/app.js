// assets/js/app.js

// Tab switching
document.querySelectorAll('.tab[data-target]').forEach(btn => {
  btn.addEventListener('click', () => {
    const group = btn.closest('.tabs').dataset.group || 'default';
    document.querySelectorAll(`.tab[data-group="${group}"], .tab:not([data-group])`);
    const allTabs   = btn.closest('.tabs').querySelectorAll('.tab');
    const allPanels = document.querySelectorAll('.tab-panel[data-group="' + (btn.dataset.group || btn.closest('.tabs').dataset.group || 'main') + '"]');
    allTabs.forEach(t => t.classList.remove('active'));
    allPanels.forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const target = document.getElementById(btn.dataset.target);
    if (target) target.classList.add('active');
  });
});

// Generic tab init (simple sibling approach)
function initTabs(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const tabs   = container.querySelectorAll('.tab');
  const panels = container.querySelectorAll('.tab-panel');
  tabs.forEach((tab, i) => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      if (panels[i]) panels[i].classList.add('active');
    });
  });
}

// Toast
function toast(msg, type = 'success') {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.style.borderColor = type === 'error' ? 'var(--rd)' : 'var(--gn)';
  el.style.color       = type === 'error' ? 'var(--rd)' : 'var(--gn)';
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2800);
}

// Flash from URL param
(function() {
  const url = new URL(window.location.href);
  const msg = url.searchParams.get('msg');
  const err = url.searchParams.get('err');
  if (msg) { setTimeout(() => toast(decodeURIComponent(msg)), 200); }
  if (err) { setTimeout(() => toast(decodeURIComponent(err), 'error'), 200); }
  // Clean URL
  if (msg || err) {
    url.searchParams.delete('msg');
    url.searchParams.delete('err');
    window.history.replaceState({}, '', url.toString());
  }
})();

// Confirm before delete / condemn
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  });
});

// Auto-dismiss PHP flash alerts
document.querySelectorAll('.flash-alert').forEach(el => {
  setTimeout(() => el.style.display = 'none', 4000);
});

// Number formatting helper
function fmt(n) { return '₹' + Number(n).toLocaleString('en-IN', {minimumFractionDigits:2}); }
