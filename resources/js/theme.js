const THEME_KEY = 'theme';

export function initTheme() {
    const lama = localStorage.getItem('landing-theme');
    if (lama && !localStorage.getItem(THEME_KEY)) {
        localStorage.setItem(THEME_KEY, lama);
    }
    localStorage.removeItem('landing-theme');
    applyTheme(localStorage.getItem(THEME_KEY) === 'light');
}

function applyTheme(isLight) {
    document.documentElement.classList.toggle('light', isLight);
    document.querySelectorAll('.theme-label').forEach((el) => {
        el.textContent = isLight ? 'Mode Gelap' : 'Mode Terang';
    });
}

export function setupThemeToggles() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-theme-toggle]');
        if (!btn) return;
        const isLight = !document.documentElement.classList.contains('light');
        localStorage.setItem(THEME_KEY, isLight ? 'light' : 'dark');
        applyTheme(isLight);
    });
}
