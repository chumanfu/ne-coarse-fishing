@props([
    'label' => 'Photo',
])

<div
    x-data
    x-show="$store.photoLightbox.openIndex !== null"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 p-4"
    @keydown.escape.window="$store.photoLightbox.close()"
    @keydown.arrow-left.window="$store.photoLightbox.prev()"
    @keydown.arrow-right.window="$store.photoLightbox.next()"
    role="dialog"
    aria-modal="true"
    :aria-label="$store.photoLightbox.label || @js($label)"
>
    <button type="button" class="absolute inset-0 cursor-zoom-out" @click="$store.photoLightbox.close()" aria-label="Close photo"></button>

    <div class="relative z-10 max-w-5xl w-full h-full flex flex-col gap-3" @click.stop>
        <div class="shrink-0 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="$store.photoLightbox.zoomIn()" class="px-3 py-1.5 rounded-md bg-white text-sm font-semibold text-slate-900">Zoom in</button>
                <button type="button" @click="$store.photoLightbox.zoomOut()" class="px-3 py-1.5 rounded-md bg-white text-sm font-semibold text-slate-900">Zoom out</button>
                <button type="button" @click="$store.photoLightbox.resetZoom()" class="px-3 py-1.5 rounded-md bg-white text-sm font-semibold text-slate-900">Reset</button>
                <template x-if="$store.photoLightbox.photos.length > 1">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="$store.photoLightbox.prev()" class="px-3 py-1.5 rounded-md bg-white text-sm font-semibold text-slate-900">Previous</button>
                        <button type="button" @click="$store.photoLightbox.next()" class="px-3 py-1.5 rounded-md bg-white text-sm font-semibold text-slate-900">Next</button>
                    </div>
                </template>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm font-semibold text-white" x-show="$store.photoLightbox.photos.length > 1">
                    <span x-text="($store.photoLightbox.openIndex ?? 0) + 1"></span>
                    /
                    <span x-text="$store.photoLightbox.photos.length"></span>
                </p>
                <button type="button" @click="$store.photoLightbox.close()" class="px-3 py-1.5 rounded-md bg-white text-sm font-semibold text-slate-900">Close</button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-auto rounded-lg bg-slate-950/40 p-2 flex">
            <img
                :src="$store.photoLightbox.current?.url"
                :alt="$store.photoLightbox.current?.alt || @js($label)"
                class="photo-lightbox__image"
                :style="{ '--photo-zoom': $store.photoLightbox.scale }"
                draggable="false"
            >
        </div>
    </div>
</div>
