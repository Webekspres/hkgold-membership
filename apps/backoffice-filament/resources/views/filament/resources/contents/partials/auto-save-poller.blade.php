@php
    use Filament\Support\Icons\Heroicon;

    $isPersisted = filled($this->getRecord()?->getKey());
@endphp

<div
    x-data="contentDraftStorage({
        isPersisted: @js($isPersisted),
        storageKey: 'hkgold-content-draft',
    })"
    class="pointer-events-none -mt-2 mb-2 flex justify-end"
    aria-live="polite"
    aria-label="Status penyimpanan otomatis"
>
    <div
        wire:loading
        wire:target="save, create, getDraftSnapshot, restoreDraftSnapshot"
        class="text-primary-600 dark:text-primary-400"
    >
        {{ \Filament\Support\generate_icon_html(
            Heroicon::OutlinedArrowPath,
            attributes: new \Illuminate\View\ComponentAttributeBag([
                'class' => 'size-6 animate-spin',
            ]),
        ) }}
    </div>

    <template x-if="! isPersisted">
        <div
            wire:loading.remove
            wire:target="save, create, getDraftSnapshot, restoreDraftSnapshot"
            class="text-primary-600 dark:text-primary-400"
            x-show="draftSaved"
            x-cloak
        >
            {{ \Filament\Support\generate_icon_html(
                Heroicon::OutlinedDevicePhoneMobile,
                attributes: new \Illuminate\View\ComponentAttributeBag([
                    'class' => 'size-6',
                ]),
            ) }}
        </div>
    </template>

    @if ($isPersisted)
        <div
            wire:loading.remove
            wire:target="save, create, getDraftSnapshot, restoreDraftSnapshot"
            class="text-primary-600 dark:text-primary-400"
        >
            {{ \Filament\Support\generate_icon_html(
                Heroicon::OutlinedCloud,
                attributes: new \Illuminate\View\ComponentAttributeBag([
                    'class' => 'size-6',
                ]),
            ) }}
        </div>
    @endif
</div>
