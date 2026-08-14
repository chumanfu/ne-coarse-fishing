import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Alpine = Alpine;

const THEME_KEY = 'necf-theme';

function forceLightTheme() {
    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';

    try {
        localStorage.removeItem(THEME_KEY);
    } catch (e) {
        // Ignore private-mode / blocked storage.
    }
}

forceLightTheme();

document.addEventListener('alpine:init', () => {
    Alpine.store('photoLightbox', {
        photos: [],
        openIndex: null,
        scale: 1,
        label: 'Photo',
        get current() {
            if (this.openIndex === null) {
                return null;
            }

            return this.photos[this.openIndex] ?? null;
        },
        normalize(photos) {
            return (photos || [])
                .map((photo) => {
                    if (typeof photo === 'string') {
                        return { url: photo, alt: 'Photo' };
                    }

                    if (photo && typeof photo === 'object') {
                        return {
                            url: photo.url || photo.src || '',
                            alt: photo.alt || 'Photo',
                        };
                    }

                    return null;
                })
                .filter((photo) => photo && photo.url);
        },
        open(photos, index = 0, label = 'Photo') {
            const items = this.normalize(photos);
            if (! items.length) {
                return;
            }

            this.photos = items;
            this.openIndex = Math.min(Math.max(0, index), items.length - 1);
            this.scale = 1;
            this.label = label || 'Photo';
            document.documentElement.classList.add('overflow-hidden');
        },
        close() {
            this.openIndex = null;
            this.scale = 1;
            this.photos = [];
            document.documentElement.classList.remove('overflow-hidden');
        },
        prev() {
            if (this.openIndex === null || this.photos.length < 2) {
                return;
            }

            this.openIndex = (this.openIndex - 1 + this.photos.length) % this.photos.length;
            this.scale = 1;
        },
        next() {
            if (this.openIndex === null || this.photos.length < 2) {
                return;
            }

            this.openIndex = (this.openIndex + 1) % this.photos.length;
            this.scale = 1;
        },
        zoomIn() {
            this.scale = Math.min(4, Number((this.scale + 0.35).toFixed(2)));
        },
        zoomOut() {
            this.scale = Math.max(1, Number((this.scale - 0.35).toFixed(2)));
        },
        resetZoom() {
            this.scale = 1;
        },
    });
});

Livewire.start();
