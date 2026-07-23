@php
    /** @var \App\Models\Reward $reward */
    $reward = $this->getRecord();
@endphp

<style>
    .hk-reward-header {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .dark .hk-reward-header {
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    .hk-reward-header__content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .hk-reward-header__title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: -0.025em;
        color: rgb(17, 24, 39);
    }

    .dark .hk-reward-header__title {
        color: rgb(249, 250, 251);
    }

    .hk-reward-header__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.375rem;
        font-size: 0.875rem;
        color: rgb(107, 114, 128);
    }

    .dark .hk-reward-header__meta {
        color: rgb(156, 163, 175);
    }

    .hk-reward-header__points {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.375rem;
        padding-top: 0.125rem;
    }

    .hk-reward-header__points-row {
        display: flex;
        align-items: baseline;
        gap: 0.375rem;
    }

    .hk-reward-header__points-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        color: rgb(232, 160, 32);
        letter-spacing: -0.025em;
    }

    .dark .hk-reward-header__points-value {
        color: rgb(251, 191, 36);
    }

    .hk-reward-header__points-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(107, 114, 128);
    }

    .dark .hk-reward-header__points-label {
        color: rgb(156, 163, 175);
    }

    .hk-reward-header__badge {
        display: inline-flex;
    }

    @media (max-width: 639px) {
        .hk-reward-header {
            flex-direction: column;
            align-items: stretch;
        }

        .hk-reward-header__points {
            align-items: flex-start;
        }
    }
</style>

<div class="hk-reward-header">
    <div class="hk-reward-header__content">
        <h2 class="hk-reward-header__title">
            {{ $reward->name }}
        </h2>
        <div class="hk-reward-header__meta">
            <span>{{ $reward->categoryReward?->name ?? '—' }}</span>
        </div>
    </div>

    <div class="hk-reward-header__points">
        <div class="hk-reward-header__points-row">
            <span class="hk-reward-header__points-value">
                {{ number_format($reward->points_required, 0, '.', ',') }}
            </span>
            <span class="hk-reward-header__points-label">Poin</span>
        </div>
        <span class="hk-reward-header__badge">
            @if ($reward->is_active)
                <x-filament::badge color="success">Aktif</x-filament::badge>
            @else
                <x-filament::badge color="danger">Nonaktif</x-filament::badge>
            @endif
        </span>
    </div>
</div>
