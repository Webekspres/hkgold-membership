import Cropper from 'cropperjs';

function coverImageUploader(config) {
    return {
        statePath: config.statePath,
        state: config.initialState ?? {},
        signedUrlEndpoint: config.signedUrlEndpoint,
        csrfToken: config.csrfToken,
        cropper: null,
        isCropping: false,
        isProcessing: false,
        statusMessage: 'Pilih gambar untuk crop 4:3, lalu otomatis dikonversi ke WebP (maks 300 KB).',

        openPicker() {
            if (this.isProcessing) {
                return;
            }

            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }

                this.initCropper(file);
            };
            input.click();
        },

        initCropper(file) {
            this.destroyCropper();
            this.isCropping = true;

            const objectUrl = URL.createObjectURL(file);

            this.$nextTick(() => {
                const image = this.$refs.cropImage;
                if (!image) {
                    return;
                }

                image.src = objectUrl;
                image.onload = () => {
                    this.cropper = new Cropper(image, {
                        aspectRatio: 4 / 3,
                        viewMode: 1,
                        autoCropArea: 1,
                        responsive: true,
                    });
                };
            });
        },

        cancelCrop() {
            this.isCropping = false;
            this.destroyCropper();
        },

        async confirmCrop() {
            if (!this.cropper || this.isProcessing) {
                return;
            }

            this.isProcessing = true;
            this.statusMessage = 'Memproses gambar...';

            try {
                const canvas = this.cropper.getCroppedCanvas({
                    width: 1200,
                    height: 900,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                const blob = await this.toCompressedWebpBlob(canvas, 300 * 1024);
                const signed = await this.requestSignedUrl();
                await this.uploadToR2(signed.upload_url, blob);

                const fileName = signed.key.split('/').pop() ?? 'cover.webp';
                const metadata = {
                    key: signed.key,
                    public_url: signed.public_url,
                    file_name: fileName,
                    file_size: blob.size,
                    file_type: 'image/webp',
                };

                this.state = metadata;
                this.$wire.set(this.statePath, metadata);
                this.statusMessage = 'Upload berhasil ke Cloudflare R2.';
            } catch (error) {
                this.statusMessage = error instanceof Error ? error.message : 'Upload gagal, silakan coba lagi.';
            } finally {
                this.isProcessing = false;
                this.isCropping = false;
                this.destroyCropper();
            }
        },

        clearImage() {
            this.state = {};
            this.$wire.set(this.statePath, null);
            this.statusMessage = 'Gambar dihapus dari form.';
        },

        async requestSignedUrl() {
            const response = await fetch(this.signedUrlEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    folder: 'contents',
                    mime: 'image/webp',
                }),
            });

            if (!response.ok) {
                throw new Error('Gagal mendapatkan signed URL.');
            }

            return response.json();
        },

        async uploadToR2(uploadUrl, blob) {
            this.statusMessage = 'Mengunggah ke Cloudflare R2...';

            const response = await fetch(uploadUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'image/webp',
                },
                body: blob,
            });

            if (!response.ok) {
                throw new Error('Upload ke Cloudflare R2 gagal.');
            }
        },

        async toCompressedWebpBlob(canvas, maxSizeBytes) {
            for (let quality = 0.9; quality >= 0.5; quality -= 0.05) {
                const blob = await new Promise((resolve) =>
                    canvas.toBlob(resolve, 'image/webp', Number(quality.toFixed(2))),
                );

                if (!blob) {
                    continue;
                }

                if (blob.size <= maxSizeBytes) {
                    return blob;
                }
            }

            const fallback = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', 0.45));

            if (!fallback) {
                throw new Error('Gagal mengonversi gambar ke WebP.');
            }

            if (fallback.size > maxSizeBytes) {
                throw new Error('Ukuran gambar masih di atas 300 KB setelah kompresi.');
            }

            return fallback;
        },

        destroyCropper() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        formatSize(size) {
            if (!size) {
                return '0 KB';
            }

            return `${Math.max(1, Math.round(size / 1024))} KB`;
        },
    };
}

function registerCoverImageUploader() {
    window.Alpine.data('coverImageUploader', coverImageUploader);
}

if (window.Alpine) {
    registerCoverImageUploader();
}

document.addEventListener('alpine:init', registerCoverImageUploader);

export default coverImageUploader;
