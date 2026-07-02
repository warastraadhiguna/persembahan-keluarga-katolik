<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Pencatatan Persembahan</h1>
    </x-slot>

    <livewire:record-transaction />

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
        <script>
            window.__scanQrFromFile = function (file) {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    const url = URL.createObjectURL(file);

                    img.onload = function () {
                        URL.revokeObjectURL(url);

                        // Coba beberapa ukuran — QR kadang lebih mudah terbaca di resolusi tertentu
                        const sizes = [800, 1200, 400];
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        for (const maxSize of sizes) {
                            const scale = Math.min(1, maxSize / Math.max(img.width, img.height));
                            canvas.width  = Math.round(img.width  * scale);
                            canvas.height = Math.round(img.height * scale);
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                            const result = jsQR(imageData.data, imageData.width, imageData.height, {
                                inversionAttempts: 'attemptBoth',
                            });

                            if (result && result.data) {
                                resolve(result.data);
                                return;
                            }
                        }

                        reject(new Error('QR tidak ditemukan'));
                    };

                    img.onerror = function () {
                        URL.revokeObjectURL(url);
                        reject(new Error('Gagal membaca gambar'));
                    };

                    img.src = url;
                });
            };
        </script>
    @endpush
</x-main-layout>
