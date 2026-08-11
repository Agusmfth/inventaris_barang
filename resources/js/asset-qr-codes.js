const initAssetQrCodes = () => {
    const container = document.querySelector('.qr-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    const boxes = [...document.querySelectorAll('.qr-selector')],
        toolbar = document.getElementById('qrSelectionToolbar'),
        count = document.getElementById('qrSelectedCount'),
        button = document.getElementById('printSelectedQr');

    const update = () => {
        const selected = boxes.filter(box => box.checked);
        boxes.forEach(box => box.closest('.qr-card')?.classList.toggle('is-selected', box.checked));
        if (count) count.textContent = selected.length;
        if (button) button.disabled = !selected.length;
        toolbar?.classList.toggle('show', selected.length > 0);
    };

    boxes.forEach(box => box.addEventListener('change', update));

    document.getElementById('qrSelectionForm')?.addEventListener('submit', () => window.setTimeout(() => {
        if (button) {
            button.disabled = false;
            button.classList.remove('app-submit-loading');
            button.removeAttribute('aria-busy');
        }
    }, 700));

    update();
};

initAssetQrCodes();
document.addEventListener('turbo:load', initAssetQrCodes);
