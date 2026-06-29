@php
    /** @var \App\Models\Branch $branch */
    $branch = $this->getRecord();
    $hasLocation = filled($branch->location_url);
@endphp

<style>
    .hk-branch-header {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .dark .hk-branch-header {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .hk-branch-header__content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .hk-branch-header__title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: -0.025em;
    }

    .hk-branch-header__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.375rem;
        font-size: 0.875rem;
        color: rgb(107, 114, 128);
    }

    .dark .hk-branch-header__meta {
        color: rgb(156, 163, 175);
    }

    .hk-branch-header__code {
        font-weight: 500;
        color: rgb(55, 65, 81);
    }

    .dark .hk-branch-header__code {
        color: rgb(209, 213, 219);
    }

    .hk-branch-header__separator {
        color: rgb(209, 213, 219);
    }

    .dark .hk-branch-header__separator {
        color: rgb(75, 85, 99);
    }

    .hk-branch-header__actions {
        flex: 0 0 auto;
    }

    @media (max-width: 639px) {
        .hk-branch-header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="hk-branch-header">
    <div class="hk-branch-header__content">
        <h2 class="hk-branch-header__title">
            Cabang {{ $branch->name }}
        </h2>
        <div class="hk-branch-header__meta">
            <span class="hk-branch-header__code">{{ $branch->branch_code }}</span>
            @if ($branch->is_online_warehouse)
                <span class="hk-branch-header__separator" aria-hidden="true">|</span>
                <x-filament::badge color="primary">
                    Online
                </x-filament::badge>
            @endif
        </div>
    </div>

    <div class="hk-branch-header__actions">
        @if ($hasLocation)
            <x-filament::button
                tag="a"
                :href="$branch->location_url"
                target="_blank"
                rel="noopener noreferrer"
                icon="heroicon-o-map-pin"
                color="primary"
            >
                Lihat lokasi
            </x-filament::button>
        @else
            <span title="Belum ada link">
                <x-filament::button
                    disabled
                    icon="heroicon-o-map-pin"
                    color="primary"
                >
                    Lihat lokasi
                </x-filament::button>
            </span>
        @endif
    </div>
</div>
