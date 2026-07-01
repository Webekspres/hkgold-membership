<x-filament-panels::page>
    {{-- Header Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        {{-- Card 1: Total Poin --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="stat-icon-gold flex h-11 w-11 items-center justify-center rounded-full">
                    <x-heroicon-o-star class="h-6 w-6" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Poin Diinjeksi</p>
                    <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $this->getStats()['total_points_injected'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Nominal Pembelian --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="stat-icon-green flex h-11 w-11 items-center justify-center rounded-full">
                    <x-heroicon-o-banknotes class="h-6 w-6" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nominal Pembelian</p>
                    <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $this->getStats()['total_purchase_nominal'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Member Unik --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="stat-icon-blue flex h-11 w-11 items-center justify-center rounded-full">
                    <x-heroicon-o-users class="h-6 w-6" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Member Unik</p>
                    <p class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $this->getStats()['total_unique_members'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>


    {{-- Batch Meta Info --}}
    <div class="mt-2 rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-1.5">
                <x-heroicon-o-user class="h-4 w-4" />
                <span>Diupload oleh <strong class="text-gray-800 dark:text-gray-200">{{ $this->getStats()['staff_name'] }}</strong></span>
            </div>
            <div class="flex items-center gap-1.5">
                <x-heroicon-o-clock class="h-4 w-4" />
                <span>{{ $this->getStats()['uploaded_at'] }}</span>
            </div>
            @if ($this->getStats()['media_file_name'])
                <div class="flex items-center gap-1.5">
                    <x-heroicon-o-document-text class="h-4 w-4" />
                    <span>{{ $this->getStats()['media_file_name'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="mt-2">
        <div class="mb-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Baris Injeksi</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Detail setiap baris dalam batch upload ini.</p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
