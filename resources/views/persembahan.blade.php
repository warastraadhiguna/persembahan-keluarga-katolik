<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Pencatatan Persembahan</h1>
    </x-slot>

    <livewire:record-transaction />

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            window.__qrScannerInstance = null;

            window.__startQrScanner = function (elementId) {
                if (window.__qrScannerInstance) {
                    return;
                }

                const instance = new Html5Qrcode(elementId);
                window.__qrScannerInstance = instance;

                instance.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 220 },
                    (decodedText) => {
                        instance.pause(true);
                        window.dispatchEvent(new CustomEvent('qr-scanned', { detail: decodedText }));
                    },
                    () => {}
                ).catch(() => {
                    window.__qrScannerInstance = null;
                });
            };

            window.__stopQrScanner = function () {
                if (!window.__qrScannerInstance) {
                    return;
                }

                const instance = window.__qrScannerInstance;
                window.__qrScannerInstance = null;

                instance.stop().then(() => instance.clear()).catch(() => {});
            };
        </script>
    @endpush
</x-main-layout>
