<div class="flex items-center gap-2">
    <label
        class="text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400"
        for="cities-province-filter"
    >
        Provinsi
    </label>

    <x-filament::input.wrapper class="min-w-44">
        <x-filament::input.select
            id="cities-province-filter"
            wire:model.live="tableFilters.province_id.value"
        >
            <option value="">Semua provinsi</option>

            @foreach (\App\Models\Province::query()->orderBy('nama')->get(['id', 'nama']) as $province)
                <option value="{{ $province->id }}">{{ $province->nama }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
