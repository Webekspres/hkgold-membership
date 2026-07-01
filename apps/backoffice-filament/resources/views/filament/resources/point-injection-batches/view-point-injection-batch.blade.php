<x-filament-panels::page>
    <style>
        /* Bulk Update Statistics Grid */
        .bulk-update-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .bulk-update-stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        /* Stat Card */
        .bulk-update-stat-card {
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
            background-color: rgb(255 255 255);
            padding: 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .bulk-update-card-body {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Icon Wrapper */
        .bulk-update-icon-wrapper {
            display: flex;
            height: 2.75rem;
            width: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
        }

        .bulk-update-stat-icon {
            height: 1.5rem;
            width: 1.5rem;
        }

        /* Color states for icon wrappers */
        .bulk-update-icon-wrapper.stat-icon-gold {
            background-color: rgb(254 243 199); /* amber-100 */
        }
        .bulk-update-icon-wrapper.stat-icon-gold svg {
            color: rgb(217 119 6); /* amber-600 */
        }
        .bulk-update-icon-wrapper.stat-icon-green {
            background-color: rgb(209 250 229); /* emerald-100 */
        }
        .bulk-update-icon-wrapper.stat-icon-green svg {
            color: rgb(5 150 105); /* emerald-600 */
        }
        .bulk-update-icon-wrapper.stat-icon-blue {
            background-color: rgb(219 234 254); /* blue-100 */
        }
        .bulk-update-icon-wrapper.stat-icon-blue svg {
            color: rgb(37 99 235); /* blue-600 */
        }

        /* Card Text */
        .bulk-update-card-text {
            flex: 1 1 0%;
        }

        .bulk-update-card-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(107 114 128);
        }

        .bulk-update-card-value {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: rgb(17 24 39);
        }

        /* Meta Container */
        .bulk-update-meta-container {
            margin-top: 0.5rem;
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
            background-color: rgb(255 255 255);
            padding: 1rem 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .bulk-update-meta-list {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            column-gap: 1.5rem;
            row-gap: 0.5rem;
            font-size: 0.875rem;
            color: rgb(107 114 128);
        }

        .bulk-update-meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .bulk-update-meta-icon {
            height: 1rem;
            width: 1rem;
        }

        .bulk-update-meta-author {
            font-weight: 700;
            color: rgb(31 41 55);
        }

        /* Dark Mode Theme Support */
        .dark .bulk-update-stat-card {
            border-color: var(--gray-800, rgb(39 39 42));
            background-color: var(--gray-900, rgb(24 24 27));
        }
        .dark .bulk-update-icon-wrapper.stat-icon-gold {
            background-color: rgb(120 53 15 / 0.2); /* amber-900/20 */
        }
        .dark .bulk-update-icon-wrapper.stat-icon-gold svg {
            color: rgb(251 191 36); /* amber-400 */
        }
        .dark .bulk-update-icon-wrapper.stat-icon-green {
            background-color: rgb(6 78 59 / 0.2); /* emerald-900/20 */
        }
        .dark .bulk-update-icon-wrapper.stat-icon-green svg {
            color: rgb(52 211 153); /* emerald-400 */
        }
        .dark .bulk-update-icon-wrapper.stat-icon-blue {
            background-color: rgb(30 58 138 / 0.2); /* blue-900/20 */
        }
        .dark .bulk-update-icon-wrapper.stat-icon-blue svg {
            color: rgb(96 165 250); /* blue-400 */
        }
        .dark .bulk-update-card-label {
            color: var(--gray-400, rgb(161 161 170));
        }
        .dark .bulk-update-card-value {
            color: white;
        }
        .dark .bulk-update-meta-container {
            border-color: var(--gray-800, rgb(39 39 42));
            background-color: var(--gray-900, rgb(24 24 27));
        }
        .dark .bulk-update-meta-list {
            color: var(--gray-400, rgb(161 161 170));
        }
        .dark .bulk-update-meta-author {
            color: var(--gray-200, rgb(228 228 231));
        }
    </style>

    <!-- Header Stats Cards -->
    <div class="bulk-update-stats-grid">
        <!-- Card 1: Total Poin -->
        <div class="bulk-update-stat-card">
            <div class="bulk-update-card-body">
                <div class="bulk-update-icon-wrapper stat-icon-gold">
                    <x-heroicon-o-star class="bulk-update-stat-icon" />
                </div>
                <div class="bulk-update-card-text">
                    <p class="bulk-update-card-label">Total Poin Diinjeksi</p>
                    <p class="bulk-update-card-value">
                        {{ $this->getStats()['total_points_injected'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Nominal Pembelian -->
        <div class="bulk-update-stat-card">
            <div class="bulk-update-card-body">
                <div class="bulk-update-icon-wrapper stat-icon-green">
                    <x-heroicon-o-banknotes class="bulk-update-stat-icon" />
                </div>
                <div class="bulk-update-card-text">
                    <p class="bulk-update-card-label">Total Nominal Pembelian</p>
                    <p class="bulk-update-card-value">
                        {{ $this->getStats()['total_purchase_nominal'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Member Unik -->
        <div class="bulk-update-stat-card">
            <div class="bulk-update-card-body">
                <div class="bulk-update-icon-wrapper stat-icon-blue">
                    <x-heroicon-o-users class="bulk-update-stat-icon" />
                </div>
                <div class="bulk-update-card-text">
                    <p class="bulk-update-card-label">Total Member Unik</p>
                    <p class="bulk-update-card-value">
                        {{ $this->getStats()['total_unique_members'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>


    <!-- Batch Meta Info -->
    <div class="bulk-update-meta-container">
        <div class="bulk-update-meta-list">
            <div class="bulk-update-meta-item">
                <x-heroicon-o-user class="bulk-update-meta-icon" />
                <span>Diupload oleh <strong class="bulk-update-meta-author">{{ $this->getStats()['staff_name'] }}</strong></span>
            </div>
            <div class="bulk-update-meta-item">
                <x-heroicon-o-clock class="bulk-update-meta-icon" />
                <span>{{ $this->getStats()['uploaded_at'] }}</span>
            </div>
            @if ($this->getStats()['media_file_name'])
                <div class="bulk-update-meta-item">
                    <x-heroicon-o-document-text class="bulk-update-meta-icon" />
                    <span>{{ $this->getStats()['media_file_name'] }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Table -->
    <div style="margin-top: 1rem;">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
