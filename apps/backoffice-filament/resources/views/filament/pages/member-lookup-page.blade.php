<x-filament-panels::page>
    {{ $this->content }}

    @if ($this->searched && $this->notFound)
        <x-filament::section class="mt-6">
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                <x-filament::icon
                    icon="heroicon-o-magnifying-glass"
                    class="h-10 w-10 text-gray-400 dark:text-gray-500"
                />

                <p class="text-base font-medium text-gray-950 dark:text-white">
                    Member tidak ditemukan
                </p>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pastikan kode member sudah benar dan cocok persis.
                </p>
            </div>
        </x-filament::section>
    @endif

    @if ($this->member !== null)
        <div class="mt-6">
            {{ $this->getInfolistContentComponent() }}
        </div>
    @endif
</x-filament-panels::page>
