<div
    x-data="redeemTokenQrScanner()"
    x-init="init($refs.reader)"
    class="hk-redeem-qr"
>
    <style>
        .hk-redeem-qr {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .hk-redeem-qr__frame {
            position: relative;
            overflow: hidden;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: #000000;
        }

        .hk-redeem-qr__reader {
            min-height: 280px;
            width: 100%;
        }

        .hk-redeem-qr__hint {
            position: absolute;
            left: 50%;
            bottom: 1rem;
            transform: translateX(-50%);
            z-index: 2;
            max-width: calc(100% - 2rem);
            padding: 0.5rem 0.875rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.92);
            color: #374151;
            font-size: 0.8125rem;
            font-weight: 500;
            text-align: center;
            line-height: 1.35;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .hk-redeem-qr__status {
            font-size: 0.8125rem;
            color: #6b7280;
        }

        .hk-redeem-qr__error {
            border-radius: 0.625rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            padding: 0.75rem 0.875rem;
            font-size: 0.875rem;
            color: #b91c1c;
        }

        .hk-redeem-qr__success {
            border-radius: 0.625rem;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            padding: 0.75rem 0.875rem;
            font-size: 0.875rem;
            color: #166534;
        }

        .hk-redeem-qr__retry {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: underline;
            color: inherit;
        }
    </style>

    <div wire:ignore class="hk-redeem-qr__frame">
        <div
            x-ref="reader"
            class="hk-redeem-qr__reader"
        ></div>
        <p
            x-show="!scannedToken"
            x-cloak
            class="hk-redeem-qr__hint"
        >
            Arahkan kamera ke QR code di aplikasi member
        </p>
    </div>

    <div x-show="starting && !scanning" x-cloak class="hk-redeem-qr__status">
        Membuka kamera...
    </div>

    <div
        x-show="error"
        x-cloak
        class="hk-redeem-qr__error"
    >
        <p x-text="error"></p>
        <p x-show="cameraDenied" class="mt-1 text-xs">
            Izinkan akses kamera di browser. Di produksi, pastikan backoffice diakses lewat HTTPS.
        </p>
        <button type="button" class="hk-redeem-qr__retry" @click="retryScanner()">
            Coba lagi
        </button>
    </div>

    <div
        x-show="scannedToken"
        x-cloak
        class="hk-redeem-qr__success"
    >
        Token terbaca: <strong x-text="scannedToken"></strong>. Melanjutkan ke verifikasi OTP...
    </div>
</div>
