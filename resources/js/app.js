import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Alpine = Alpine;

const THEME_KEY = 'necf-theme';

function preferredDark() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function readStoredTheme() {
    try {
        return localStorage.getItem(THEME_KEY);
    } catch (e) {
        return null;
    }
}

function applyTheme(dark) {
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
}

document.addEventListener('alpine:init', () => {
    const stored = readStoredTheme();
    const dark = stored === 'dark' || (stored !== 'light' && preferredDark());

    applyTheme(dark);

    Alpine.store('theme', {
        dark,
        toggle() {
            this.dark = ! this.dark;
            applyTheme(this.dark);
            try {
                localStorage.setItem(THEME_KEY, this.dark ? 'dark' : 'light');
            } catch (e) {
                // Ignore private-mode / blocked storage.
            }
        },
    });
});

Livewire.start();
