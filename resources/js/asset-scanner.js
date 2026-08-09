import { Html5Qrcode } from 'html5-qrcode';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('assetScanner');
    if (!root || root.dataset.ready) return;
    root.dataset.ready = '1';
    const reader = new Html5Qrcode('qrReader', { verbose: false });
    const startButton = document.getElementById('startScanner'), stopButton = document.getElementById('stopScanner'), switchButton = document.getElementById('switchCamera');
    const placeholder = document.getElementById('scannerPlaceholder'), result = document.getElementById('scannerResult');
    const manualForm = document.getElementById('manualAssetForm'), manualCode = document.getElementById('manualAssetCode');
    let cameras = [], cameraIndex = 0, scanning = false, resolving = false;

    const showResult = (type, title, message, retry = false) => {
        result.className = `scanner-result ${type}`; result.hidden = false;
        result.replaceChildren();
        const heading = document.createElement('strong'), copy = document.createElement('span');
        heading.textContent = title; copy.textContent = message; result.append(heading, copy);
        if (retry) {
            const retryButton = document.createElement('button'); retryButton.type = 'button'; retryButton.textContent = 'Scan Lagi';
            retryButton.addEventListener('click', () => { result.hidden = true; resolving = false; start(); }); result.append(retryButton);
        }
    };
    const stop = async () => {
        if (!scanning) return;
        scanning = false;
        try { await reader.stop(); } catch (_) {}
        placeholder.classList.remove('is-hidden'); startButton.hidden = false; stopButton.hidden = true; switchButton.hidden = true;
    };
    const resolveAsset = async (value, mode) => {
        if (resolving) return;
        resolving = true;
        if (mode === 'scan') await stop();
        showResult('info', 'Memeriksa QR Code', 'Mencocokkan data dengan inventaris sekolah.');
        try {
            const response = await fetch(root.dataset.resolveUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ value, mode }) });
            const data = await response.json();
            if (!response.ok) { showResult('error', data.title || 'Data tidak dapat diproses', data.message || Object.values(data.errors || {}).flat()[0], true); return; }
            showResult('success', mode === 'scan' ? 'QR berhasil dipindai' : 'Aset ditemukan', `${data.name} · ${data.asset_code}`);
            window.setTimeout(() => window.location.assign(data.redirect_url), 500);
        } catch (_) { showResult('error', 'Koneksi bermasalah', 'Data aset belum dapat diperiksa. Periksa koneksi lalu coba lagi.', true); }
    };
    const start = async () => {
        if (scanning || resolving) return;
        if (!window.isSecureContext && !['localhost', '127.0.0.1'].includes(window.location.hostname)) { showResult('error', 'Kamera memerlukan HTTPS', 'Buka aplikasi melalui koneksi HTTPS yang aman.'); return; }
        startButton.disabled = true; result.hidden = true;
        try {
            if (!cameras.length) {
                cameras = await Html5Qrcode.getCameras();
                const rear = cameras.findIndex(camera => /back|rear|environment/i.test(camera.label));
                if (rear >= 0) cameraIndex = rear;
            }
            if (!cameras.length) throw new Error('NO_CAMERA');
            await reader.start(cameras[cameraIndex].id, { fps: 10, qrbox: (width, height) => { const size = Math.floor(Math.min(width, height) * .62); return { width: size, height: size }; } }, text => resolveAsset(text, 'scan'), () => {});
            scanning = true; placeholder.classList.add('is-hidden'); startButton.hidden = true; stopButton.hidden = false; switchButton.hidden = cameras.length < 2;
        } catch (error) {
            const denied = error?.name === 'NotAllowedError' || /permission|denied|notallowed/i.test(String(error));
            showResult('error', denied ? 'Izin kamera ditolak' : 'Kamera tidak tersedia', denied ? 'Izinkan akses kamera pada pengaturan browser, lalu tekan Mulai Kamera.' : 'Kamera tidak ditemukan atau sedang digunakan aplikasi lain. Gunakan pencarian kode aset.');
        } finally { startButton.disabled = false; }
    };
    startButton.addEventListener('click', start);
    stopButton.addEventListener('click', stop);
    switchButton.addEventListener('click', async () => { await stop(); cameraIndex = (cameraIndex + 1) % cameras.length; resolving = false; await start(); });
    manualForm.addEventListener('submit', event => { event.preventDefault(); const value = manualCode.value.trim(); if (!value) { showResult('error', 'Kode aset belum diisi', 'Masukkan kode aset, misalnya AST-2026-0001.'); manualCode.focus(); return; } resolving = false; resolveAsset(value, 'manual'); });
    document.addEventListener('turbo:before-visit', stop, { once: true });
    window.addEventListener('pagehide', stop, { once: true });
});
