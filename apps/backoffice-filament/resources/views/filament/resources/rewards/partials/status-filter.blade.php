<div class="flex items-center gap-2">
    <label
        class="text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400"
        for="rewards-status-filter"
    >
        Status
    </label>

    <x-filament::input.wrapper class="min-w-44">
        <x-filament::input.select
            id="rewards-status-filter"
            wire:model.live="tableFilters.is_active.value"
        >
            <option value="">Semua</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
