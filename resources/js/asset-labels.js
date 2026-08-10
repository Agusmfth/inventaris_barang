document.addEventListener('DOMContentLoaded', () => {
    const selectors = [...document.querySelectorAll('.asset-selector')];
    const selectAll = document.getElementById('selectAllAssets');
    const count = document.getElementById('selectedAssetCount');
    const button = document.getElementById('labelPreviewButton');
    const liveCards = document.getElementById('labelLiveCards');
    const liveEmpty = document.getElementById('labelLiveEmpty');
    const liveCount = document.getElementById('labelLiveCount');
    const form = document.getElementById('labelSelectionForm');
    if (!form || !count || !button) return;

    const schoolName = form.dataset.schoolName || 'KOPERASI DESA';
    const schoolMark = form.dataset.schoolMark || 'INVENTARIS\nKOPERASI DESA';
    const schoolLogo = form.dataset.schoolLogo || '';

    const renderPreviewCard = (row) => {
        const d = row.dataset;
        const logoHtml = schoolLogo 
            ? `<img src="${schoolLogo}" alt="Logo" style="width:24px;height:24px;object-fit:contain;flex-shrink:0">` 
            : `<span class="llc-emblem">INV</span>`;
            
        return `<div class="label-live-card" data-preview-id="${d.assetId}">
            <div class="llc-header">
                <div class="llc-brand">${logoHtml}<div><strong>${schoolName}</strong><small>SISTEM INVENTARIS &amp; ASET</small></div></div>
                <div class="llc-mark">◆ ${schoolMark.replace(/\n/g, '<br>')}</div>
            </div>
            <div class="llc-banner">BARANG INVENTARIS</div>
            <div class="llc-body">
                <div class="llc-info">
                    <div class="llc-title"><span>▣</span><div><small>NAMA ASET</small><strong>${d.assetName}</strong></div></div>
                    <dl>
                        <div><dt>Kode Inventaris</dt><dd>${d.assetCode}</dd></div>
                        <div><dt>Kategori</dt><dd>${d.assetCategory}</dd></div>
                        <div><dt>Lokasi / Ruangan</dt><dd>${d.assetLocation}</dd></div>
                        <div><dt>Tahun Pengadaan</dt><dd>${d.assetYear}</dd></div>
                        <div><dt>Kondisi</dt><dd><span class="llc-badge">${d.assetCondition}</span></dd></div>
                        <div><dt>Status</dt><dd><span class="llc-badge llc-badge-status">${d.assetStatus}</span></dd></div>
                    </dl>
                </div>
                <div class="llc-qr">
                    <small>SCAN UNTUK INFORMASI</small>
                    <img src="${d.assetQrImage}" alt="QR" style="width:52px;height:52px;background:#fff;border-radius:2px">
                    <small>Aset Milik<b>${schoolName}</b></small>
                </div>
            </div>
            <div class="llc-footer">♢ JAGA &amp; GUNAKAN DENGAN BAIK</div>
        </div>`;
    };

    const update = () => {
        const selected = selectors.filter(item => item.checked);
        selectors.forEach(item => {
            const quantity = item.closest('tr').querySelector('.label-quantity input');
            quantity.disabled = !item.checked;
            item.closest('tr').classList.toggle('is-selected', item.checked);
        });
        count.textContent = selected.length;
        const totalLabels = selected.reduce((total, item) => total + Number(item.closest('tr').querySelector('.label-quantity input').value || 1), 0);
        button.disabled = selected.length === 0;
        button.querySelector('span').textContent = selected.length ? `Preview & Cetak ${totalLabels} Label` : 'Preview & Cetak Label';
        if (selectAll) {
            selectAll.checked = selectors.length > 0 && selected.length === selectors.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < selectors.length;
        }

        // Update live preview panel
        if (liveCards && liveEmpty && liveCount) {
            liveCount.textContent = `${selected.length} dipilih`;
            if (selected.length === 0) {
                liveEmpty.style.display = '';
                liveCards.innerHTML = '';
            } else {
                liveEmpty.style.display = 'none';
                const selectedIds = new Set(selected.map(cb => cb.value));
                // Remove cards no longer selected
                liveCards.querySelectorAll('[data-preview-id]').forEach(card => {
                    if (!selectedIds.has(card.dataset.previewId)) card.remove();
                });
                // Add new cards
                selected.forEach(cb => {
                    if (!liveCards.querySelector(`[data-preview-id="${cb.value}"]`)) {
                        const row = cb.closest('tr');
                        liveCards.insertAdjacentHTML('beforeend', renderPreviewCard(row));
                    }
                });
            }
        }
    };

    selectors.forEach(item => item.addEventListener('change', update));
    document.querySelectorAll('.label-quantity input').forEach(item => item.addEventListener('input', update));
    selectAll?.addEventListener('change', () => { selectors.forEach(item => item.checked = selectAll.checked); update(); });
    document.addEventListener('app:submit-reset', update, { once: true });
    update();
});
