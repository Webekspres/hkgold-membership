@php
    use App\Enums\InjectionStatus;

    $statusOptions = [
        InjectionStatus::Pending->value => 'Pending',
        InjectionStatus::Validated->value => 'Tervalidasi',
        InjectionStatus::Success->value => 'Sukses',
        InjectionStatus::Failed->value => 'Gagal',
    ];
@endphp

<div class="bulk-update-status-filter">
    <label class="bulk-update-status-filter-label" for="bulk-update-status-filter">
        Status
    </label>

    <x-filament::input.wrapper>
        <x-filament::input.select
            id="bulk-update-status-filter"
            wire:model.live="tableFilters.status.value"
        >
            <option value="">Semua status</option>

            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
