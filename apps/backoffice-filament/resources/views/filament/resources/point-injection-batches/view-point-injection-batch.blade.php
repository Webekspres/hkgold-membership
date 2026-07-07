<x-filament-panels::page>
    <style>
        /* Bulk Update Statistics Grid */
        .bulk-update-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
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

        .bulk-update-view {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Meta Container */
        .bulk-update-meta-container {
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

        /* Progress Section */
        .bulk-update-progress-container {
            border-radius: 0.75rem;
            border: 1px solid rgb(219 234 254);
            background-color: rgb(239 246 255);
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .bulk-update-progress-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .bulk-update-spinner {
            height: 1.25rem;
            width: 1.25rem;
            animation: bulk-update-spin 1s linear infinite;
            color: rgb(37 99 235);
        }

        @keyframes bulk-update-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .bulk-update-progress-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(30 64 175);
        }

        .bulk-update-progress-track {
            height: 0.5rem;
            width: 100%;
            overflow: hidden;
            border-radius: 9999px;
            background-color: rgb(219 234 254);
        }

        .bulk-update-progress-fill {
            height: 100%;
            border-radius: 9999px;
            background-color: rgb(37 99 235);
            transition: width 0.3s ease;
        }

        .bulk-update-progress-fill.indeterminate {
            width: 40% !important;
            animation: bulk-update-indeterminate 1.5s ease-in-out infinite;
        }

        @keyframes bulk-update-indeterminate {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }

        .bulk-update-progress-caption {
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: rgb(59 130 246);
        }

        /* Summary Section */
        .bulk-update-summary-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .bulk-update-summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .bulk-update-summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border-radius: 0.5rem;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .bulk-update-summary-chip.chip-success {
            background-color: rgb(209 250 229);
            color: rgb(6 95 70);
        }

        .bulk-update-summary-chip.chip-danger {
            background-color: rgb(254 226 226);
            color: rgb(185 28 28);
        }

        .bulk-update-summary-chip-icon {
            height: 1rem;
            width: 1rem;
        }

        .bulk-update-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            font-size: 0.875rem;
        }

        .bulk-update-alert.alert-warning {
            border: 1px solid rgb(253 230 138);
            background-color: rgb(255 251 235);
            color: rgb(146 64 14);
        }

        .bulk-update-alert.alert-danger {
            border: 1px solid rgb(254 202 202);
            background-color: rgb(254 242 242);
            color: rgb(185 28 28);
        }

        .bulk-update-alert-icon {
            height: 1.125rem;
            width: 1.125rem;
            flex-shrink: 0;
            margin-top: 0.0625rem;
        }

        .dark .bulk-update-progress-container {
            border-color: rgb(30 58 138 / 0.4);
            background-color: rgb(30 58 138 / 0.15);
        }

        .dark .bulk-update-progress-title {
            color: rgb(147 197 253);
        }

        .dark .bulk-update-progress-track {
            background-color: rgb(30 58 138 / 0.3);
        }

        .dark .bulk-update-progress-fill {
            background-color: rgb(96 165 250);
        }

        .dark .bulk-update-progress-caption {
            color: rgb(147 197 253);
        }

        .dark .bulk-update-summary-chip.chip-success {
            background-color: rgb(6 78 59 / 0.25);
            color: rgb(110 231 183);
        }

        .dark .bulk-update-summary-chip.chip-danger {
            background-color: rgb(127 29 29 / 0.25);
            color: rgb(252 165 165);
        }

        .dark .bulk-update-alert.alert-warning {
            border-color: rgb(120 53 15 / 0.4);
            background-color: rgb(120 53 15 / 0.15);
            color: rgb(253 230 138);
        }

        .dark .bulk-update-alert.alert-danger {
            border-color: rgb(127 29 29 / 0.4);
            background-color: rgb(127 29 29 / 0.15);
            color: rgb(252 165 165);
        }
    </style>

    <div class="bulk-update-view">
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

    @php($progress = $this->getProgressStats())

    @if ($progress['is_finalizing'])
        <div wire:poll.5s="refreshBatch" class="bulk-update-progress-container">
            <div class="bulk-update-progress-header">
                <x-heroicon-o-arrow-path class="bulk-update-spinner" />
                <span class="bulk-update-progress-title">Sedang memproses ke PointMutation...</span>
            </div>
            <div class="bulk-update-progress-track">
                @if ($progress['finalize_percent'] !== null)
                    <div
                        class="bulk-update-progress-fill"
                        style="width: {{ $progress['finalize_percent'] }}%;"
                    ></div>
                @else
                    <div class="bulk-update-progress-fill indeterminate"></div>
                @endif
            </div>
            <p class="bulk-update-progress-caption">
                {{ number_format($progress['success_rows'], 0, ',', '.') }}
                dari
                {{ number_format($progress['validated_rows'], 0, ',', '.') }}
                baris selesai
                @if ($progress['finalize_percent'] !== null)
                    ({{ $progress['finalize_percent'] }}%)
                @endif
            </p>
        </div>
    @elseif ($progress['is_processing'])
        <div wire:poll.5s="refreshBatch" class="bulk-update-progress-container">
            <div class="bulk-update-progress-header">
                <x-heroicon-o-arrow-path class="bulk-update-spinner" />
                <span class="bulk-update-progress-title">Sedang memproses baris...</span>
            </div>
            <div class="bulk-update-progress-track">
                @if ($progress['percent'] !== null)
                    <div
                        class="bulk-update-progress-fill"
                        style="width: {{ $progress['percent'] }}%;"
                    ></div>
                @else
                    <div class="bulk-update-progress-fill indeterminate"></div>
                @endif
            </div>
            <p class="bulk-update-progress-caption">
                @if ($progress['total_rows'] > 0)
                    {{ number_format($progress['processed_rows'], 0, ',', '.') }}
                    dari
                    {{ number_format($progress['total_rows'], 0, ',', '.') }}
                    baris selesai
                    @if ($progress['percent'] !== null)
                        ({{ $progress['percent'] }}%)
                    @endif
                @else
                    Membaca dan memvalidasi file...
                @endif
            </p>
        </div>
    @else
        <div class="bulk-update-summary-container">
            @if ($progress['is_stale'] && $progress['can_retry_import'])
                <div class="bulk-update-alert alert-warning">
                    <x-heroicon-o-exclamation-triangle class="bulk-update-alert-icon" />
                    <span>
                        Pemrosesan mungkin gagal — gunakan tombol <strong>Ulangi Parsing</strong> di header
                        atau pastikan queue worker berjalan
                        (<code>php artisan queue:work redis --queue=bulk-injection</code>).
                    </span>
                </div>
            @elseif ($progress['is_stale'])
                <div class="bulk-update-alert alert-warning">
                    <x-heroicon-o-exclamation-triangle class="bulk-update-alert-icon" />
                    <span>
                        Pemrosesan mungkin gagal — pastikan queue worker berjalan
                        (<code>php artisan queue:work redis --queue=bulk-injection</code>).
                    </span>
                </div>
            @endif

            @if ($progress['is_resolved'])
                <div class="bulk-update-alert" style="border: 1px solid rgb(167 243 208); background-color: rgb(236 253 245); color: rgb(6 95 70);">
                    <x-heroicon-o-check-badge class="bulk-update-alert-icon" />
                    <span>Batch ini sudah selesai diproses. Halaman ini bersifat view-only.</span>
                </div>
            @endif

            @if ($progress['total_rows'] > 0 || $progress['validated_rows'] > 0 || $progress['failed_rows'] > 0)
                <div class="bulk-update-summary-row">
                    <span class="bulk-update-summary-chip chip-success">
                        <x-heroicon-o-check-circle class="bulk-update-summary-chip-icon" />
                        {{ number_format($progress['validated_rows'], 0, ',', '.') }} baris tervalidasi
                    </span>
                    @if ($progress['failed_rows'] > 0)
                        <span class="bulk-update-summary-chip chip-danger">
                            <x-heroicon-o-x-circle class="bulk-update-summary-chip-icon" />
                            {{ number_format($progress['failed_rows'], 0, ',', '.') }} baris gagal
                        </span>
                    @endif
                </div>
            @endif

            @if ($progress['failed_rows'] > 0)
                <div class="bulk-update-alert alert-danger">
                    <x-heroicon-o-exclamation-circle class="bulk-update-alert-icon" />
                    <span>
                        Terdapat baris yang gagal divalidasi. Perbaiki atau hapus sebelum memproses.
                    </span>
                </div>
            @endif
        </div>
    @endif

    <!-- Detail Table -->
    <div>
        {{ $this->table }}
    </div>
    </div>
</x-filament-panels::page>
