(function () {
    const storageKey = 'ia-manager-theme';
    const root = document.documentElement;

    function preferredTheme() {
        const saved = localStorage.getItem(storageKey);

        if (saved === 'light' || saved === 'dark') {
            return saved;
        }

        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    function applyTheme(theme) {
        root.dataset.theme = theme;
        localStorage.setItem(storageKey, theme);

        const button = document.getElementById('themeToggle');

        if (button) {
            button.textContent = theme === 'dark' ? 'Modo claro' : 'Modo oscuro';
        }
    }

    window.toggleTheme = function () {
        applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
    };

    window.addEventListener('DOMContentLoaded', function () {
        applyTheme(preferredTheme());

        const header = document.querySelector('header');

        if (header && !document.getElementById('themeToggle')) {
            const button = document.createElement('button');
            button.id = 'themeToggle';
            button.className = 'themeBtn';
            button.type = 'button';
            button.onclick = window.toggleTheme;
            header.appendChild(button);
            applyTheme(root.dataset.theme);
        }
    });
})();
