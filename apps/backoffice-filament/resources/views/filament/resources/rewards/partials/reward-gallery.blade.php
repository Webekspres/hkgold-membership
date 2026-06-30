@php
    /** @var \App\Models\Reward $reward */
    $reward = $this->getRecord();
    $images = $reward->rewardImages->filter(fn ($ri) => $ri->media?->file_url)->values();
@endphp

<style>
    .hk-gallery {
        width: 100%;
    }

    .hk-gallery__grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    @media (min-width: 640px) {
        .hk-gallery__grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .hk-gallery__grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .hk-gallery__thumb {
        position: relative;
        width: 100%;
        padding-top: 56.25%; /* 16:9 */
        cursor: pointer;
        border-radius: 0.5rem;
        overflow: hidden;
        background-color: rgb(243, 244, 246);
    }

    .dark .hk-gallery__thumb {
        background-color: rgb(31, 41, 55);
    }

    .hk-gallery__thumb img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }

    .hk-gallery__thumb:hover img {
        transform: scale(1.04);
    }

    .hk-gallery__thumb-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0);
        transition: background 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hk-gallery__thumb:hover .hk-gallery__thumb-overlay {
        background: rgba(0, 0, 0, 0.25);
    }

    .hk-gallery__zoom-icon {
        opacity: 0;
        transition: opacity 0.2s ease;
        color: white;
    }

    .hk-gallery__thumb:hover .hk-gallery__zoom-icon {
        opacity: 1;
    }

    .hk-gallery__empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 2.5rem 1rem;
        border-radius: 0.5rem;
        border: 1px dashed rgb(209, 213, 219);
        color: rgb(156, 163, 175);
    }

    .dark .hk-gallery__empty {
        border-color: rgb(55, 65, 81);
        color: rgb(107, 114, 128);
    }

    /* Lightbox */
    .hk-lightbox {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(4px);
    }

    .hk-lightbox__img-wrap {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hk-lightbox__img-wrap img {
        max-width: 90vw;
        max-height: 85vh;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .hk-lightbox__btn {
        position: fixed;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        border-radius: 9999px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        transition: background 0.15s ease;
    }

    .hk-lightbox__btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .hk-lightbox__close {
        top: 1.25rem;
        right: 1.25rem;
    }

    .hk-lightbox__prev {
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
    }

    .hk-lightbox__next {
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
    }

    .hk-lightbox__counter {
        position: fixed;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        font-size: 0.875rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }
</style>

@if ($images->isEmpty())
    <div class="hk-gallery__empty">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 2.5rem; height: 2.5rem;">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <span>Belum ada gambar</span>
    </div>
@else
    <div
        class="hk-gallery"
        x-data="{
            open: false,
            current: 0,
            images: {{ Js::from($images->map(fn ($ri) => $ri->media->file_url)->values()) }},
            total: {{ $images->count() }},
            show(index) {
                this.current = index;
                this.open = true;
                document.body.style.overflow = 'hidden';
            },
            close() {
                this.open = false;
                document.body.style.overflow = '';
            },
            prev() {
                this.current = (this.current - 1 + this.total) % this.total;
            },
            next() {
                this.current = (this.current + 1) % this.total;
            },
        }"
        @keydown.escape.window="open && close()"
        @keydown.arrow-left.window="open && prev()"
        @keydown.arrow-right.window="open && next()"
    >
        <div class="hk-gallery__grid">
            @foreach ($images as $index => $rewardImage)
                <div
                    class="hk-gallery__thumb"
                    @click="show({{ $index }})"
                    role="button"
                    tabindex="0"
                    @keydown.enter="show({{ $index }})"
                    @keydown.space.prevent="show({{ $index }})"
                >
                    <img
                        src="{{ $rewardImage->media->file_url }}"
                        alt="{{ $rewardImage->media->caption ?? $reward->name }}"
                        loading="lazy"
                    />
                    <div class="hk-gallery__thumb-overlay">
                        <svg class="hk-gallery__zoom-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 1.75rem; height: 1.75rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Lightbox --}}
        <div
            class="hk-lightbox"
            x-show="open"
            x-cloak
            @click.self="close()"
            style="display: none;"
        >
            <div class="hk-lightbox__img-wrap">
                <template x-for="(url, i) in images" :key="i">
                    <img
                        :src="url"
                        x-show="current === i"
                        alt="Gambar reward"
                    />
                </template>
            </div>

            {{-- Close --}}
            <button class="hk-lightbox__btn hk-lightbox__close" @click="close()" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Prev --}}
            <button
                class="hk-lightbox__btn hk-lightbox__prev"
                @click="prev()"
                x-show="total > 1"
                aria-label="Sebelumnya"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>

            {{-- Next --}}
            <button
                class="hk-lightbox__btn hk-lightbox__next"
                @click="next()"
                x-show="total > 1"
                aria-label="Berikutnya"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            {{-- Counter --}}
            <div class="hk-lightbox__counter" x-show="total > 1">
                <span x-text="current + 1"></span>/<span x-text="total"></span>
            </div>
        </div>
    </div>
@endif
