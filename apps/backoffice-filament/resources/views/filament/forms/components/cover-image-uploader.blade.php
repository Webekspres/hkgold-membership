@php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();
    $state = $getState();
    $state = is_array($state) ? $state : [];
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
>
    <div
        wire:ignore
        x-data="coverImageUploader({
            statePath: @js($statePath),
            initialState: @js($state),
            signedUrlEndpoint: @js($getSignedUrlEndpoint()),
            csrfToken: @js(csrf_token()),
        })"
        class="space-y-3"
    >
        <template x-if="state.public_url">
            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <img x-bind:src="state.public_url" alt="Preview cover" class="h-52 w-full rounded-md object-cover">
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="state.file_name"></span>
                    <span class="mx-1">-</span>
                    <span x-text="formatSize(state.file_size)"></span>
                </div>
            </div>
        </template>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="fi-btn fi-btn-size-md fi-btn-outlined fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-950 hover:fi-text-color-800 dark:fi-text-color-0 dark:hover:fi-text-color-0"
                x-on:click="openPicker()"
                x-bind:disabled="isProcessing"
            >
                <span x-show="! state.public_url">Pilih Gambar</span>
                <span x-show="state.public_url" x-cloak>Ganti Gambar</span>
            </button>

            <button
                type="button"
                class="fi-btn fi-btn-size-md fi-btn-outlined fi-color fi-color-danger fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-950 hover:fi-text-color-800 dark:fi-text-color-0 dark:hover:fi-text-color-0"
                x-show="state.public_url"
                x-on:click="clearImage()"
                x-bind:disabled="isProcessing"
                x-cloak
            >
                Hapus
            </button>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="statusMessage"></p>

        <template x-if="isCropping">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4">
                <div class="w-full max-w-3xl rounded-xl bg-white p-4 dark:bg-gray-900">
                    <div class="mb-3 text-sm font-semibold">Crop Cover (4:3)</div>
                    <div class="max-h-[70vh] overflow-auto">
                        <img x-ref="cropImage" alt="Crop preview" class="mx-auto block max-h-[65vh] max-w-full">
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            type="button"
                            class="fi-btn fi-btn-size-md fi-btn-outlined"
                            x-on:click="cancelCrop()"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="fi-btn fi-btn-size-md fi-btn-outlined fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-950 hover:fi-text-color-800 dark:fi-text-color-0 dark:hover:fi-text-color-0"
                            x-on:click="confirmCrop()"
                        >
                            Gunakan
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>


@once
    @vite('resources/js/app.js')
@endonce
