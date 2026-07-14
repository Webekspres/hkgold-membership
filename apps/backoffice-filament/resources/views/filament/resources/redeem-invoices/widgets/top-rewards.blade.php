<x-filament-widgets::widget>
    <x-filament::section heading="Reward Terbanyak Ditukar" description="Top 5 dalam 30 hari terakhir">
        <style>
            .redeem-top-rewards-list {
                display: grid;
                gap: 0.75rem;
            }

            .redeem-top-reward-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                border: 1px solid rgba(148, 163, 184, 0.3);
                border-radius: 0.75rem;
                padding: 0.5rem 0.75rem;
                background: rgba(255, 255, 255, 0.96);
            }

            .dark .redeem-top-reward-item {
                background: rgba(17, 24, 39, 0.88);
                border-color: rgba(148, 163, 184, 0.22);
            }

            .redeem-top-reward-left {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                min-width: 0;
            }

            .redeem-top-reward-rank {
                font-size: 0.75rem;
                font-weight: 700;
                color: rgb(148 163 184);
                width: 1rem;
                text-align: center;
                flex-shrink: 0;
            }

            .redeem-top-reward-name {
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(15 23 42);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dark .redeem-top-reward-name {
                color: rgb(241 245 249);
            }

            .redeem-top-reward-count {
                font-size: 0.875rem;
                font-weight: 700;
                color: rgb(202 138 4);
                flex-shrink: 0;
            }

            .redeem-top-rewards-empty {
                font-size: 0.875rem;
                color: rgb(100 116 139);
            }

            .dark .redeem-top-rewards-empty {
                color: rgb(148 163 184);
            }
        </style>

        <div class="redeem-top-rewards-list">
            @forelse ($topRewards as $index => $reward)
                <div class="redeem-top-reward-item">
                    <div class="redeem-top-reward-left">
                        <span class="redeem-top-reward-rank">{{ $index + 1 }}</span>
                        <span class="redeem-top-reward-name">
                            {{ $reward->reward_name }}
                        </span>
                    </div>
                    <span class="redeem-top-reward-count">
                        {{ number_format((int) $reward->redeem_count, 0, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="redeem-top-rewards-empty">Belum ada data redeem 30 hari terakhir.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
