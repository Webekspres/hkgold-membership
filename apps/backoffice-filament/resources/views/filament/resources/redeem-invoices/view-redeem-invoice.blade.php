@php
    $stats = $this->getStats();
    $rewardImages = $stats['reward_images'] ?? [];
@endphp

<x-filament-panels::page>
    <style>
        .redeem-invoice-view {
            display: grid;
            gap: 1rem;
        }

        .redeem-member-card {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
        }

        .dark .redeem-member-card {
            background: rgba(17, 24, 39, 0.88);
            border-color: rgba(148, 163, 184, 0.22);
        }

        .redeem-member-photo {
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .redeem-member-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem 1rem;
            width: 100%;
        }

        .redeem-field-label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: rgb(100 116 139);
            margin-bottom: 0.15rem;
        }

        .redeem-field-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: rgb(15 23 42);
        }

        .dark .redeem-field-value {
            color: rgb(241 245 249);
        }

        .redeem-detail-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 1fr);
        }

        .redeem-detail-card {
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            padding: 1rem;
        }

        .dark .redeem-detail-card {
            background: rgba(17, 24, 39, 0.88);
            border-color: rgba(148, 163, 184, 0.22);
        }

        .redeem-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: rgb(51 65 85);
            margin-bottom: 0.75rem;
        }

        .dark .redeem-card-title {
            color: rgb(203 213 225);
        }

        .redeem-field-stack {
            display: grid;
            gap: 0.65rem;
        }

        .reward-slider {
            position: relative;
            border-radius: 0.875rem;
            overflow: hidden;
            margin-bottom: 0.875rem;
            background: rgb(241 245 249);
            aspect-ratio: 16 / 9;
        }

        .dark .reward-slider {
            background: rgb(30 41 59);
        }

        .reward-slider-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .reward-slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            border: 0;
            background: rgba(15, 23, 42, 0.65);
            color: #fff;
            cursor: pointer;
        }

        .reward-slider-btn.prev {
            left: 0.5rem;
        }

        .reward-slider-btn.next {
            right: 0.5rem;
        }

        .reward-slider-dots {
            display: flex;
            gap: 0.4rem;
            justify-content: center;
            margin-top: 0.5rem;
        }

        .reward-slider-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            border: 0;
            background: rgb(203 213 225);
            cursor: pointer;
        }

        .reward-slider-dot.active {
            background: rgb(232 160 32);
        }

        @media (min-width: 1024px) {
            .redeem-detail-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
            }

            .redeem-member-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }
    </style>

    <div class="redeem-invoice-view">
        <div class="redeem-member-card">
            @if (filled($stats['member_photo'] ?? null))
                <img class="redeem-member-photo" src="{{ $stats['member_photo'] }}" alt="Member photo">
            @else
                <img class="redeem-member-photo" src="https://ui-avatars.com/api/?name={{ urlencode($stats['member_name']) }}&background=random" alt="Member photo">
            @endif

            <div class="redeem-member-grid">
                <div>
                    <span class="redeem-field-label">Nama Member</span>
                    <span class="redeem-field-value">{{ $stats['member_name'] }}</span>
                </div>
                <div>
                    <span class="redeem-field-label">Kode Member</span>
                    <span class="redeem-field-value">{{ $stats['member_number'] }}</span>
                </div>
                <div>
                    <span class="redeem-field-label">Tier</span>
                    <span class="redeem-field-value">{{ $stats['member_tier'] }}</span>
                </div>
                <div>
                    <span class="redeem-field-label">No. HP</span>
                    <span class="redeem-field-value">{{ $stats['member_phone'] }}</span>
                </div>
                <div>
                    <span class="redeem-field-label">Email</span>
                    <span class="redeem-field-value">{{ $stats['member_email'] }}</span>
                </div>
                <div>
                    <span class="redeem-field-label">No. Invoice</span>
                    <span class="redeem-field-value">{{ $stats['invoice_number'] }}</span>
                </div>
            </div>
        </div>

        <div class="redeem-detail-grid">
            <section class="redeem-detail-card">
                <h3 class="redeem-card-title">Informasi Cabang & Staff</h3>
                <div class="redeem-field-stack">
                    <div>
                        <span class="redeem-field-label">Nama Cabang</span>
                        <span class="redeem-field-value">{{ $stats['branch_name'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Lokasi Cabang</span>
                        <span class="redeem-field-value">{{ $stats['branch_address'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Link Lokasi</span>
                        @if (filled($stats['branch_location_url'] ?? null))
                            <a class="redeem-field-value" href="{{ $stats['branch_location_url'] }}" target="_blank">Lihat maps</a>
                        @else
                            <span class="redeem-field-value">-</span>
                        @endif
                    </div>
                    <div>
                        <span class="redeem-field-label">Nama Staff</span>
                        <span class="redeem-field-value">{{ $stats['staff_name'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Waktu Penukaran</span>
                        <span class="redeem-field-value">{{ $stats['redeemed_at'] ?? '-' }}</span>
                    </div>
                </div>
            </section>

            <section class="redeem-detail-card">
                <h3 class="redeem-card-title">Informasi Reward</h3>
                <div
                    x-data="{ images: @js($rewardImages), index: 0 }"
                >
                    <template x-if="images.length > 0">
                        <div>
                            <div class="reward-slider">
                                <img class="reward-slider-image" :src="images[index]" alt="Reward image">
                                <button
                                    x-show="images.length > 1"
                                    class="reward-slider-btn prev"
                                    type="button"
                                    @click="index = (index - 1 + images.length) % images.length"
                                >
                                    &#8592;
                                </button>
                                <button
                                    x-show="images.length > 1"
                                    class="reward-slider-btn next"
                                    type="button"
                                    @click="index = (index + 1) % images.length"
                                >
                                    &#8594;
                                </button>
                            </div>

                            <div x-show="images.length > 1" class="reward-slider-dots">
                                <template x-for="(_, dotIndex) in images" :key="dotIndex">
                                    <button
                                        type="button"
                                        class="reward-slider-dot"
                                        :class="{ 'active': dotIndex === index }"
                                        @click="index = dotIndex"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="images.length === 0">
                        <div class="reward-slider">
                            <div class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada gambar reward.
                            </div>
                        </div>
                    </template>
                </div>

                <div class="redeem-field-stack">
                    <div>
                        <span class="redeem-field-label">Nama Reward</span>
                        <span class="redeem-field-value">{{ $stats['reward_name'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Kategori</span>
                        <span class="redeem-field-value">{{ $stats['reward_category'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">SKU</span>
                        <span class="redeem-field-value">{{ $stats['reward_sku'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Deskripsi</span>
                        <span class="redeem-field-value">{{ $stats['reward_description'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Poin Dibutuhkan</span>
                        <span class="redeem-field-value">{{ $stats['reward_points_required'] }}</span>
                    </div>
                    <div>
                        <span class="redeem-field-label">Poin Ditukarkan (Invoice)</span>
                        <span class="redeem-field-value">{{ $stats['invoice_points_redeemed'] }}</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
