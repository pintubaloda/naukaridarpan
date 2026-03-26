/**
 * Naukaridarpan — Main Frontend JS
 * Handles: search autocomplete, exam timer, file upload UX,
 * parse status polling, notification toasts, price calculator
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── CSRF token helper ──────────────────────────────────────────────
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const post = (url, data) => fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify(data),
  }).then(r => r.json());

  // ── TOAST NOTIFICATIONS ───────────────────────────────────────────
  window.toast = (msg, type = 'success', duration = 4000) => {
    const el = document.createElement('div');
    el.className = `nd-toast nd-toast-${type}`;
    el.textContent = msg;
    el.style.cssText = `
      position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
      padding:.75rem 1.25rem;border-radius:10px;font-family:'Space Grotesk',sans-serif;
      font-size:.9rem;font-weight:500;max-width:380px;line-height:1.4;
      box-shadow:0 8px 24px rgba(0,0,0,.15);
      background:${type==='success'?'#276749':type==='error'?'#C53030':'#0D5C63'};
      color:#fff;opacity:0;transition:opacity .25s;
    `;
    document.body.appendChild(el);
    requestAnimationFrame(() => el.style.opacity = '1');
    setTimeout(() => {
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 300);
    }, duration);
  };

  // ── SEARCH AUTOCOMPLETE ───────────────────────────────────────────
  const searchInputs = document.querySelectorAll('.navbar-search input, .hero-search input');
  searchInputs.forEach(input => {
    let debounceTimer;
    let dropdown = null;

    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = input.value.trim();
      if (q.length < 2) { dropdown?.remove(); dropdown = null; return; }

      debounceTimer = setTimeout(async () => {
        try {
          const res = await fetch(`/api/v1/exams?search=${encodeURIComponent(q)}&per_page=6`);
          const data = await res.json();
          renderAutocomplete(input, data.data || []);
        } catch (e) { /* silent */ }
      }, 300);
    });

    input.addEventListener('keydown', e => {
      if (e.key === 'Escape') { dropdown?.remove(); dropdown = null; }
    });

    document.addEventListener('click', e => {
      if (!input.contains(e.target)) { dropdown?.remove(); dropdown = null; }
    });

    function renderAutocomplete(input, results) {
      dropdown?.remove();
      if (!results.length) return;

      dropdown = document.createElement('div');
      dropdown.style.cssText = `
        position:absolute;top:calc(100% + 4px);left:0;right:0;
        background:#fff;border:1px solid #E8E0D5;border-radius:10px;
        box-shadow:0 10px 30px rgba(0,0,0,.12);z-index:200;overflow:hidden;
      `;

      results.forEach(exam => {
        const item = document.createElement('a');
        item.href = `/exams/${exam.slug}`;
        item.style.cssText = `
          display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;
          text-decoration:none;color:#2D3748;font-family:'Space Grotesk',sans-serif;
          font-size:.88rem;border-bottom:1px solid #F5F0EB;transition:background .15s;
        `;
        item.onmouseenter = () => item.style.background = '#FDF0E6';
        item.onmouseleave = () => item.style.background = '';
        item.innerHTML = `
          <span style="font-size:1.2rem">📝</span>
          <div>
            <div style="font-weight:600">${exam.title}</div>
            <div style="font-size:.76rem;color:#718096">${exam.category?.name || ''} · ${exam.is_free ? 'Free' : '₹' + exam.student_price}</div>
          </div>
        `;
        dropdown.appendChild(item);
      });

      const viewAll = document.createElement('a');
      viewAll.href = `/exams?search=${encodeURIComponent(input.value)}`;
      viewAll.style.cssText = `display:block;padding:.6rem 1rem;text-align:center;font-size:.82rem;color:#E8650A;font-family:'Space Grotesk',sans-serif;font-weight:600;`;
      viewAll.textContent = `View all results for "${input.value}" →`;
      dropdown.appendChild(viewAll);

      const wrap = input.closest('.navbar-search, .hero-search') || input.parentElement;
      wrap.style.position = 'relative';
      wrap.appendChild(dropdown);
    }
  });

  // ── PARSE STATUS POLLING ──────────────────────────────────────────
  const parseStatusEl = document.getElementById('parse-status-indicator');
  if (parseStatusEl) {
    const paperId = parseStatusEl.dataset.paperId;
    const statusUrl = parseStatusEl.dataset.statusUrl;
    if (paperId && statusUrl && parseStatusEl.dataset.status === 'processing') {
      const poll = setInterval(async () => {
        try {
          const r = await fetch(statusUrl);
          const d = await r.json();
          if (d.status === 'done') {
            clearInterval(poll);
            parseStatusEl.className = 'parse-status ps-done';
            parseStatusEl.innerHTML = `✓ Parsed ${d.total_questions} questions successfully`;
            toast(`AI parsed ${d.total_questions} questions! Review and submit for approval.`, 'success');
            document.getElementById('submit-review-btn')?.removeAttribute('disabled');
          } else if (d.status === 'failed') {
            clearInterval(poll);
            parseStatusEl.className = 'parse-status ps-failed';
            parseStatusEl.textContent = '✗ Parsing failed: ' + (d.log || 'Unknown error');
            toast('AI parsing failed. Please check your PDF and try again.', 'error');
          } else {
            parseStatusEl.textContent = `⏳ AI is parsing your paper… (${d.total_questions || 0} questions so far)`;
          }
        } catch (e) { /* retry next tick */ }
      }, 3000);
    }
  }

  // ── PRICE CALCULATOR (seller upload) ─────────────────────────────
  const priceInput = document.getElementById('seller-price');
  const priceDisplay = document.getElementById('student-price-display');
  if (priceInput && priceDisplay) {
    const COMMISSION = 0.15;
    priceInput.addEventListener('input', () => {
      const v = parseFloat(priceInput.value) || 0;
      const free = document.querySelector('[name="is_free"]')?.checked;
      priceDisplay.textContent = free || v === 0 ? 'FREE' : '₹' + Math.ceil(v * (1 + COMMISSION));
    });
  }

  // ── DRAG & DROP UPLOAD ────────────────────────────────────────────
  const dropZone = document.getElementById('drop-zone');
  const fileInput = document.getElementById('pdf-file');
  if (dropZone && fileInput) {
    ['dragenter', 'dragover'].forEach(ev =>
      dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('drag'); })
    );
    ['dragleave', 'drop'].forEach(ev =>
      dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('drag'); })
    );
    dropZone.addEventListener('drop', e => {
      const file = e.dataTransfer.files[0];
      if (file?.type === 'application/pdf') {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFileName(file);
      } else {
        toast('Please upload a PDF file only.', 'error');
      }
    });
    fileInput.addEventListener('change', () => { if (fileInput.files[0]) showFileName(fileInput.files[0]); });
    function showFileName(file) {
      const nameEl = document.getElementById('file-name');
      if (nameEl) {
        nameEl.textContent = `✓ ${file.name} (${(file.size / 1024 / 1024).toFixed(1)} MB)`;
        nameEl.style.display = 'block';
      }
      dropZone.style.borderColor = 'var(--ok)';
    }
  }

  // ── CONFIRM DIALOGS ───────────────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.dataset.confirm)) e.preventDefault();
    });
  });

  // ── COPY TO CLIPBOARD ─────────────────────────────────────────────
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', () => {
      navigator.clipboard.writeText(btn.dataset.copy).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = orig; }, 1500);
      });
    });
  });

  // ── EXAM SECURITY (active during exam) ────────────────────────────
  if (window.__examMode) {
    // Prevent right-click
    document.addEventListener('contextmenu', e => e.preventDefault());
    // Prevent copy/paste
    document.addEventListener('copy', e => e.preventDefault());
    document.addEventListener('paste', e => e.preventDefault());
    // Block DevTools shortcuts
    document.addEventListener('keydown', e => {
      if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key)) || (e.ctrlKey && e.key === 'U')) {
        e.preventDefault();
      }
    });
    // Fullscreen prompt
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen().catch(() => {});
    }
  }

  // ── SMOOTH SCROLL for anchor links ───────────────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

});
