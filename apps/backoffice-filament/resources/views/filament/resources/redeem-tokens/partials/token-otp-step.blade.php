@php
    $error = $error ?? null;
    $tokenCode = $tokenCode ?? '';
    $memberName = $memberName ?? '-';
    $memberNumber = $memberNumber ?? '-';
    $maskedPhone = $maskedPhone ?? '****';
    $rewardName = $rewardName ?? '-';
    $rewardImages = $rewardImages ?? [];
    $pointsLabel = $pointsLabel ?? '-';
    $branchName = $branchName ?? '-';
    $branchIsCurrent = $branchIsCurrent ?? false;
    $staffName = $staffName ?? '-';
    $staffId = $staffId ?? null;
    $otpStatus = $otpStatus ?? 'SUCCESS';
    $otpStatusColor = $otpStatusColor ?? 'success';
@endphp

<style>
    .hk-redeem-otp {
        color: #111827;
    }

    .hk-redeem-otp__grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .hk-redeem-otp__grid {
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 1.25rem;
        }
    }

    .hk-redeem-otp__panel {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #ffffff;
        overflow: hidden;
    }

    .hk-redeem-otp__panel-head {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }

    .hk-redeem-otp__panel-body {
        padding: 1rem;
    }

    .hk-redeem-otp__images {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .hk-redeem-otp__image-wrap {
        position: relative;
        width: 5.5rem;
        height: 5.5rem;
        border-radius: 0.625rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .hk-redeem-otp__image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hk-redeem-otp__image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.625rem;
        color: #9ca3af;
        text-align: center;
        padding: 0.25rem;
    }

    .hk-redeem-otp__fields {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
        border: 1px solid #e5e7eb;
        border-radius: 0.625rem;
        overflow: hidden;
    }

    @media (min-width: 640px) {
        .hk-redeem-otp__fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hk-redeem-otp__field--full {
            grid-column: 1 / -1;
        }
    }

    .hk-redeem-otp__field {
        padding: 0.75rem 0.875rem;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
    }

    .hk-redeem-otp__field:nth-child(2n) {
        border-right: none;
    }

    @media (min-width: 640px) {
        .hk-redeem-otp__field:nth-last-child(-n+2):not(.hk-redeem-otp__field--full) {
            border-bottom: none;
        }
    }

    .hk-redeem-otp__field:last-child {
        border-bottom: none;
    }

    .hk-redeem-otp__field-label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .hk-redeem-otp__field-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.35;
    }

    .hk-redeem-otp__field-sub {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
    }

    .hk-redeem-otp__status-box {
        border: 1px solid #d1d5db;
        border-radius: 0.625rem;
        padding: 0.75rem 0.875rem;
        margin-bottom: 0.875rem;
        background: #f9fafb;
        font-size: 0.8125rem;
        color: #374151;
    }

    .hk-redeem-otp__status-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .hk-redeem-otp__pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.125rem 0.5rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .hk-redeem-otp__pill--success {
        background: #dcfce7;
        color: #166534;
    }

    .hk-redeem-otp__pill--danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .hk-redeem-otp__resend {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        border: none;
        border-radius: 0.625rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        background: #2563eb;
        color: #ffffff;
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .hk-redeem-otp__resend:hover:not(:disabled) {
        background: #1d4ed8;
    }

    .hk-redeem-otp__resend:disabled {
        background: #93c5fd;
        cursor: not-allowed;
    }

    .hk-redeem-otp__otp-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.625rem;
    }

    .hk-redeem-otp__digits {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .hk-redeem-otp__digit {
        width: 100%;
        aspect-ratio: 1;
        max-height: 3.25rem;
        border: 2px dashed #cbd5e1;
        border-radius: 0.625rem;
        background: #ffffff;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .hk-redeem-otp__digit:focus {
        border-color: #2563eb;
        border-style: solid;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .hk-redeem-otp__error {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 0.625rem;
        padding: 0.75rem 0.875rem;
        font-size: 0.875rem;
    }
</style>

@if ($error !== null)
    <div class="hk-redeem-otp">
        <div class="hk-redeem-otp__error">{{ $error }}</div>
    </div>
@else
    <div
        class="hk-redeem-otp"
        x-data="redeemOtpStep({
            tokenCode: @js($tokenCode),
            resendSeconds: 30,
        })"
        x-init="init()"
    >
        <div class="hk-redeem-otp__grid">
            <section class="hk-redeem-otp__panel">
                <div class="hk-redeem-otp__panel-head">Detail Transaksi Snapshot</div>
                <div class="hk-redeem-otp__panel-body">
                    <div class="hk-redeem-otp__images">
                        @forelse ($rewardImages as $imageUrl)
                            <div class="hk-redeem-otp__image-wrap">
                                <img src="{{ $imageUrl }}" alt="{{ $rewardName }}" />
                            </div>
                        @empty
                            <div class="hk-redeem-otp__image-wrap">
                                <div class="hk-redeem-otp__image-placeholder">Gambar hadiah</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="hk-redeem-otp__fields">
                        <div class="hk-redeem-otp__field">
                            <span class="hk-redeem-otp__field-label">Nama Member</span>
                            <span class="hk-redeem-otp__field-value">
                                {{ $memberName }}
                                <span class="hk-redeem-otp__field-sub">({{ $memberNumber }})</span>
                            </span>
                        </div>
                        <div class="hk-redeem-otp__field">
                            <span class="hk-redeem-otp__field-label">Hadiah</span>
                            <span class="hk-redeem-otp__field-value">{{ $rewardName }}</span>
                        </div>
                        <div class="hk-redeem-otp__field">
                            <span class="hk-redeem-otp__field-label">Poin</span>
                            <span class="hk-redeem-otp__field-value">{{ $pointsLabel }}</span>
                        </div>
                        <div class="hk-redeem-otp__field hk-redeem-otp__field--full">
                            <span class="hk-redeem-otp__field-label">Cabang Pengambilan</span>
                            <span class="hk-redeem-otp__field-value">
                                {{ $branchName }}
                                @if ($branchIsCurrent)
                                    <span class="hk-redeem-otp__field-sub">(Cabang Anda)</span>
                                @endif
                            </span>
                        </div>
                        <div class="hk-redeem-otp__field hk-redeem-otp__field--full">
                            <span class="hk-redeem-otp__field-label">Staff Melayani</span>
                            <span class="hk-redeem-otp__field-value">
                                {{ $staffName }}
                                @if ($staffId !== null)
                                    <span class="hk-redeem-otp__field-sub">(ID: {{ $staffId }})</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="hk-redeem-otp__panel">
                <div class="hk-redeem-otp__panel-head">Otorisasi Keamanan</div>
                <div class="hk-redeem-otp__panel-body">
                    <div class="hk-redeem-otp__status-box">
                        <div class="hk-redeem-otp__status-row">
                            <span>Status OTP WhatsApp:</span>
                            <span @class([
                                'hk-redeem-otp__pill',
                                'hk-redeem-otp__pill--success' => $otpStatusColor === 'success',
                                'hk-redeem-otp__pill--danger' => $otpStatusColor === 'danger',
                            ])>
                                [{{ $otpStatus }}]
                            </span>
                            <span>Sent to: {{ $maskedPhone }}</span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="hk-redeem-otp__resend"
                        :disabled="resendDisabled || resending"
                        wire:click="resendRedeemOtp(@js($tokenCode))"
                        wire:loading.attr="disabled"
                        wire:target="resendRedeemOtp"
                        @click="onResendClick()"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.555 4.126 1.528 5.867L0 24l6.335-1.662A11.944 11.944 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.006-1.378l-.358-.213-3.757.987.999-3.648-.233-.375A9.818 9.818 0 1112 21.818z"/>
                        </svg>
                        <span x-text="resendLabel"></span>
                    </button>

                    <div class="hk-redeem-otp__otp-label">Masukkan Kode OTP 6 Digit</div>
                    <div class="hk-redeem-otp__digits">
                        @foreach (range(0, 5) as $index)
                            <input
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                class="hk-redeem-otp__digit"
                                x-ref="digit{{ $index }}"
                                x-model="digits[{{ $index }}]"
                                @input="onDigitInput({{ $index }}, $event)"
                                @keydown="onDigitKeydown({{ $index }}, $event)"
                                @paste="onPaste($event)"
                                autocomplete="one-time-code"
                            />
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </div>
@endif
