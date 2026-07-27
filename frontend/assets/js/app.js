(function () {
    // ---- Dark / Light mode ----
    const root = document.documentElement;
    const stored = localStorage.getItem('mbg_theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initial = stored || (prefersDark ? 'dark' : 'light');
    root.setAttribute('data-theme', initial);

    function updateToggleIcon(theme) {
        const btn = document.getElementById('themeToggle');
        if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
    updateToggleIcon(initial);

    window.mbgToggleTheme = function () {
        const current = root.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        localStorage.setItem('mbg_theme', next);
        updateToggleIcon(next);
    };

    // ---- Mobile nav toggle ----
    window.mbgToggleNav = function () {
        const nav = document.getElementById('navLinks');
        if (nav) nav.classList.toggle('open');
    };

    // ---- Simple fetch helper for pages doing client-side calls (optional) ----
    window.mbgFlash = function (msg, type) {
        const box = document.getElementById('flashBox');
        if (!box) return;
        box.innerHTML = '<div class="alert ' + (type || 'success') + '">' + msg + '</div>';
    };
})();
