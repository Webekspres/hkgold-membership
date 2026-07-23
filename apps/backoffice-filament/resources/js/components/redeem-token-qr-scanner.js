import { Html5Qrcode } from "html5-qrcode";

const TOKEN_PATTERN = /^[A-Z0-9]{10}$/;
const TOKEN_EXTRACT_PATTERN = /[A-Z0-9]{10}/;

export function normalizeTokenCode(raw) {
    const trimmed = String(raw ?? "")
        .trim()
        .toUpperCase();

    if (TOKEN_PATTERN.test(trimmed)) {
        return trimmed;
    }

    const match = trimmed.match(TOKEN_EXTRACT_PATTERN);

    return match ? match[0] : null;
}

function redeemTokenQrScanner() {
    return {
        scanning: false,
        error: null,
        cameraDenied: false,
        scannedToken: null,
        scanner: null,
        starting: false,
        readerId: null,

        init(readerEl) {
            if (!readerEl) {
                return;
            }

            if (!readerEl.id) {
                readerEl.id = `redeem-qr-reader-${Math.random().toString(36).slice(2, 9)}`;
            }

            this.readerId = readerEl.id;

            this.$nextTick(() => {
                void this.startScanner();

                this._visibilityObserver = new IntersectionObserver(
                    ([entry]) => {
                        if (!entry.isIntersecting) {
                            void this.stopScanner();
                        } else {
                            void this.startScanner();
                        }
                    },
                    { threshold: 0.1 },
                );
                this._visibilityObserver.observe(this.$root);

                this._domObserver = new MutationObserver(() => {
                    if (!document.contains(this.$el)) {
                        this.destroy();
                    }
                });
                this._domObserver.observe(document.body, {
                    childList: true,
                    subtree: true,
                });
            });
        },

        destroy() {
            this._visibilityObserver?.disconnect();
            this._visibilityObserver = null;
            this._domObserver?.disconnect();
            this._domObserver = null;
            void this.stopScanner();
        },

        findTokenInput() {
            const modal = this.$root.closest(".fi-modal");

            return modal?.querySelector("[data-redeem-token-input]") ?? null;
        },

        async startScanner() {
            if (this.scanning || this.starting || !this.readerId) {
                return;
            }

            this.starting = true;
            this.error = null;
            this.cameraDenied = false;

            try {
                if (
                    !window.isSecureContext &&
                    window.location.hostname !== "localhost"
                ) {
                    throw new Error("Kamera membutuhkan HTTPS atau localhost.");
                }

                if (!navigator.mediaDevices?.getUserMedia) {
                    throw new Error("Browser tidak mendukung akses kamera.");
                }

                this.scanner = new Html5Qrcode(this.readerId);
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1,
                };

                try {
                    await this.scanner.start(
                        { facingMode: { exact: "environment" } },
                        config,
                        (decodedText) => this.onScanSuccess(decodedText),
                        () => {},
                    );
                } catch {
                    const cameras = await Html5Qrcode.getCameras();
                    if (cameras.length === 0) {
                        throw new Error("Tidak ada kamera yang terdeteksi.");
                    }

                    const preferred =
                        cameras.find((camera) =>
                            /back|rear|environment/i.test(camera.label),
                        ) ?? cameras[0];

                    await this.scanner.start(
                        preferred.id,
                        config,
                        (decodedText) => this.onScanSuccess(decodedText),
                        () => {},
                    );
                }

                this.scanning = true;
            } catch (exception) {
                const message =
                    exception instanceof Error
                        ? exception.message
                        : "Gagal membuka kamera.";
                this.error = message;
                this.cameraDenied =
                    /permission|denied|notallowed|not allowed/i.test(message) ||
                    exception?.name === "NotAllowedError";
            } finally {
                this.starting = false;
            }
        },

        async stopScanner() {
            if (!this.scanner) {
                return;
            }

            try {
                if (this.scanning) {
                    await this.scanner.stop();
                }
                await this.scanner.clear();
            } catch {
                // ponytail: ignore teardown races when modal closes mid-frame
            } finally {
                this.scanner = null;
                this.scanning = false;
            }
        },

        async onScanSuccess(decodedText) {
            const token = normalizeTokenCode(decodedText);
            if (!token) {
                this.error = "QR tidak berisi kode token redeem yang valid.";

                return;
            }

            this.scannedToken = token;
            this.error = null;
            await this.applyTokenToForm(token);
            void this.stopScanner();
            await this.advanceWizard();
        },

        async applyTokenToForm(token) {
            const input = this.findTokenInput();

            const wireModel =
                input?.getAttribute("wire:model") ??
                input?.getAttribute("wire:model.live") ??
                input?.getAttribute("wire:model.blur");

            if (wireModel && this.$wire?.set) {
                await this.$wire.set(wireModel, token);
            } else if (wireModel) {
                const wireRoot = input?.closest("[wire\\:id]");
                const wireId = wireRoot?.getAttribute("wire:id");
                const component = wireId ? window.Livewire?.find(wireId) : null;

                if (component?.set) {
                    await component.set(wireModel, token);
                }
            }

            if (!input) {
                return;
            }

            input.value = token;
            input.dispatchEvent(new Event("input", { bubbles: true }));
            input.dispatchEvent(new Event("change", { bubbles: true }));
        },

        async advanceWizard() {
            await this.$nextTick();

            const modal = this.$root.closest(".fi-modal");
            const wizard = modal?.querySelector(".fi-sc-wizard");

            if (!wizard) {
                return;
            }

            const wizardData = window.Alpine?.$data(wizard);
            if (wizardData?.requestNextStep) {
                await wizardData.requestNextStep();
            }
        },

        retryScanner() {
            this.error = null;
            this.cameraDenied = false;
            this.scannedToken = null;
            void this.stopScanner().then(() => this.startScanner());
        },
    };
}

function redeemOtpStep({ tokenCode, resendSeconds = 30 }) {
    return {
        tokenCode,
        digits: ["", "", "", "", "", ""],
        resendDisabled: true,
        resendSeconds,
        resendRemaining: resendSeconds,
        resendTimer: null,
        resending: false,

        get resendLabel() {
            if (this.resendDisabled) {
                return `KIRIM ULANG OTP (Tunggu: ${this.resendRemaining}s)`;
            }

            return "KIRIM ULANG OTP";
        },

        init() {
            this.syncFromHiddenInput();
            this.startCountdown();
            this.$nextTick(() => this.$refs.digit0?.focus());
        },

        startCountdown() {
            this.resendDisabled = true;
            this.resendRemaining = this.resendSeconds;
            clearInterval(this.resendTimer);

            this.resendTimer = setInterval(() => {
                if (this.resendRemaining <= 1) {
                    clearInterval(this.resendTimer);
                    this.resendTimer = null;
                    this.resendDisabled = false;
                    this.resendRemaining = 0;

                    return;
                }

                this.resendRemaining -= 1;
            }, 1000);
        },

        onResendClick() {
            this.resending = true;
            this.$nextTick(() => {
                this.resending = false;
                this.startCountdown();
            });
        },

        onDigitInput(index, event) {
            const value = String(event.target.value ?? "")
                .replace(/\D/g, "")
                .slice(-1);
            this.digits[index] = value;
            event.target.value = value;

            if (value && index < 5) {
                this.$refs[`digit${index + 1}`]?.focus();
            }

            this.syncToHiddenInput();
        },

        onDigitKeydown(index, event) {
            if (event.key === "Backspace" && !this.digits[index] && index > 0) {
                this.$refs[`digit${index - 1}`]?.focus();
            }
        },

        onPaste(event) {
            event.preventDefault();
            const pasted = String(event.clipboardData?.getData("text") ?? "")
                .replace(/\D/g, "")
                .slice(0, 6);

            if (pasted.length === 0) {
                return;
            }

            pasted.split("").forEach((char, index) => {
                this.digits[index] = char;
            });

            const focusIndex = Math.min(pasted.length, 5);
            this.$refs[`digit${focusIndex}`]?.focus();
            this.syncToHiddenInput();
        },

        syncFromHiddenInput() {
            const input = this.findOtpInput();
            if (!input?.value) {
                return;
            }

            input.value
                .replace(/\D/g, "")
                .slice(0, 6)
                .split("")
                .forEach((char, index) => {
                    this.digits[index] = char;
                });
        },

        syncToHiddenInput() {
            const otp = this.digits.join("");
            const input = this.findOtpInput();

            if (!input) {
                return;
            }

            input.value = otp;
            input.dispatchEvent(new Event("input", { bubbles: true }));
            input.dispatchEvent(new Event("change", { bubbles: true }));

            const wireModel =
                input.getAttribute("wire:model") ??
                input.getAttribute("wire:model.live") ??
                input.getAttribute("wire:model.blur");

            if (wireModel && this.$wire?.set) {
                void this.$wire.set(wireModel, otp);
            }
        },

        findOtpInput() {
            const modal = this.$root.closest(".fi-modal");

            return modal?.querySelector("[data-redeem-otp-input]") ?? null;
        },

        destroy() {
            clearInterval(this.resendTimer);
        },
    };
}

function registerRedeemTokenQrScanner() {
    window.Alpine.data("redeemTokenQrScanner", redeemTokenQrScanner);
    window.Alpine.data("redeemOtpStep", redeemOtpStep);
}

if (window.Alpine) {
    registerRedeemTokenQrScanner();
}

document.addEventListener("alpine:init", registerRedeemTokenQrScanner);

export default redeemTokenQrScanner;
