@php
    $roleOptions = \App\Filament\Resources\Staff\Support\StaffRoleSupport::staffRoleOptions();
@endphp

<div class="flex items-center gap-2">
    <label
        class="text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400"
        for="staff-role-filter"
    >
        Role
    </label>

    <x-filament::input.wrapper class="min-w-44">
        <x-filament::input.select
            id="staff-role-filter"
            wire:model.live="tableFilters.role.value"
        >
            <option value="">Semua role</option>

            @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
