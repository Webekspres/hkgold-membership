<div class="flex items-center gap-2">
    <label
        class="text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400"
        for="point-injection-batches-resolved-filter"
    >
        Status Penyelesaian
    </label>

    <x-filament::input.wrapper class="min-w-44">
        <x-filament::input.select
            id="point-injection-batches-resolved-filter"
            wire:model.live="tableFilters.resolved.value"
        >
            <option value="">Semua</option>
            <option value="1">Selesai</option>
            <option value="0">Belum Diselesaikan</option>
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
