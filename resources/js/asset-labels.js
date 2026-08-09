document.addEventListener('DOMContentLoaded', () => {
    const selectors = [...document.querySelectorAll('.asset-selector')];
    const selectAll = document.getElementById('selectAllAssets');
    const count = document.getElementById('selectedAssetCount');
    const button = document.getElementById('labelPreviewButton');
    if (!document.getElementById('labelSelectionForm') || !count || !button) return;
    const update = () => {
        const selected = selectors.filter(item => item.checked);
        selectors.forEach(item => { const quantity = item.closest('tr').querySelector('.label-quantity input'); quantity.disabled = !item.checked; item.closest('tr').classList.toggle('is-selected', item.checked); });
        count.textContent = selected.length;
        const totalLabels = selected.reduce((total,item) => total + Number(item.closest('tr').querySelector('.label-quantity input').value || 1), 0);
        button.disabled = selected.length === 0;
        button.querySelector('span').textContent = selected.length ? `Preview & Cetak ${totalLabels} Label` : 'Preview & Cetak Label';
        if (selectAll) {
            selectAll.checked = selectors.length > 0 && selected.length === selectors.length;
            selectAll.indeterminate = false;
        }
    };
    selectors.forEach(item => item.addEventListener('change', update));
    document.querySelectorAll('.label-quantity input').forEach(item => item.addEventListener('input', update));
    selectAll?.addEventListener('change', () => { selectors.forEach(item => item.checked = selectAll.checked); update(); });
    document.addEventListener('app:submit-reset', update, { once: true });
    update();
});
