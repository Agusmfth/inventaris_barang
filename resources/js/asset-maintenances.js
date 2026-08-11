const initAssetMaintenances = () => {
    const container = document.querySelector('.maintenance-page, .maintenance-detail-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    const toolbarDate = document.querySelector('.maintenance-toolbar input[type="date"]');
    if (toolbarDate) {
        const wrapper = document.createElement('div');
        wrapper.className = 'maintenance-date-filter';
        const placeholder = document.createElement('span');
        placeholder.textContent = 'Tanggal laporan';
        toolbarDate.parentNode.insertBefore(wrapper, toolbarDate);
        wrapper.append(toolbarDate, placeholder);
        const syncDate = () => wrapper.classList.toggle('has-value', Boolean(toolbarDate.value));
        toolbarDate.addEventListener('change', syncDate);
        toolbarDate.addEventListener('input', syncDate);
        syncDate();
    }
    const trackTemplate = document.getElementById('maintenanceTrackTemplate');
    const detailSidebar = document.querySelector('.maintenance-detail-layout aside');
    if (trackTemplate && detailSidebar) {
        detailSidebar.append(trackTemplate.content.cloneNode(true));
        window.refreshLucideIcons?.();
    }
    const digits = value => String(value ?? '').replace(/\D/g, '');
    const formatRupiah = value => digits(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    document.querySelectorAll('#estimatedCost, input[name="actual_cost"]').forEach(input => {
        input.type = 'text';
        input.inputMode = 'numeric';
        input.removeAttribute('min');
        input.removeAttribute('step');
        const wrapper = document.createElement('div');
        wrapper.className = 'maintenance-currency';
        const prefix = document.createElement('span');
        prefix.textContent = 'Rp';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.append(prefix, input);
        input.value = formatRupiah(input.value || '0');
        input.addEventListener('input', () => { input.value = formatRupiah(input.value) });
        input.form?.addEventListener('submit', () => { input.value = digits(input.value) || '0' });
    });
    const originalNotes = document.querySelector('#completeMaintenanceModal textarea[name="notes"]');
    if (originalNotes) {
        originalNotes.readOnly = true;
        originalNotes.classList.add('maintenance-notes-readonly');
        originalNotes.removeAttribute('name');
        const label = originalNotes.closest('.category-form-group')?.querySelector('label');
        if (label) label.textContent = 'Catatan Awal';
    }
    const errorForm = document.querySelector('[data-has-errors="1"][data-error-modal]');
    if (errorForm) bootstrap.Modal.getOrCreateInstance(document.getElementById(errorForm.dataset.errorModal)).show();
    const form = document.getElementById('maintenanceForm');
    if (!form) return;
    const search = document.getElementById('maintenanceAssetSearch');
    const select = document.getElementById('maintenanceAsset');
    const results = document.getElementById('maintenanceAssetResults');
    const meta = document.querySelectorAll('#maintenanceAssetMeta b');
    const condition = document.getElementById('initialCondition');
    const conditionDisplay = document.createElement('input');
    conditionDisplay.type = 'text';
    conditionDisplay.className = 'form-control maintenance-condition-readonly';
    conditionDisplay.value = 'Pilih aset terlebih dahulu';
    conditionDisplay.readOnly = true;
    conditionDisplay.setAttribute('aria-label', 'Kondisi awal aset otomatis');
    condition.hidden = true;
    condition.required = false;
    condition.insertAdjacentElement('afterend', conditionDisplay);
    select.selectedIndex = -1;
    const close = () => results.classList.remove('show');
    const choose = option => {
        select.value = option.value;
        search.value = option.text.trim();
        search.setCustomValidity('');
        meta[0].textContent = option.dataset.location;
        meta[1].textContent = option.dataset.condition;
        meta[2].textContent = option.dataset.status;
        condition.value = option.dataset.conditionKey;
        conditionDisplay.value = option.dataset.condition;
        close();
    };
    const render = () => {
        const needle = search.value.toLowerCase();
        results.innerHTML = '';
        [...select.options].filter(option => option.text.toLowerCase().includes(needle)).slice(0, 8).forEach(option => {
            const button = document.createElement('button');
            const parts = option.text.split('—');
            button.type = 'button'; button.className = 'maintenance-asset-option';
            button.innerHTML = `<b>${parts[0]}</b><span>${parts.slice(1).join('—')}</span><small>${option.dataset.location}</small>`;
            button.onmousedown = event => { event.preventDefault(); choose(option) };
            results.appendChild(button);
        });
        results.classList.add('show');
    };
    search.onfocus = render;
    search.oninput = () => { select.selectedIndex = -1; condition.value = ''; conditionDisplay.value = 'Pilih aset terlebih dahulu'; render() };
    search.onblur = () => setTimeout(close, 120);
    document.getElementById('reviewMaintenance').onclick = () => {
        if (!select.value) search.setCustomValidity('Pilih aset dari daftar.');
        if (!form.reportValidity()) return;
        const option = select.options[select.selectedIndex];
        const value = id => document.getElementById(id).value || '—';
        document.getElementById('maintenanceConfirm').innerHTML = `<div><dt>Aset</dt><dd>${option.text}</dd></div><div><dt>Keluhan</dt><dd>${value('maintenanceIssue')}</dd></div><div><dt>Kondisi</dt><dd>${option.dataset.condition}</dd></div><div><dt>Estimasi biaya</dt><dd>Rp ${formatRupiah(value('estimatedCost'))}</dd></div><div><dt>Status</dt><dd>Menunggu</dd></div>`;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('maintenanceModal')).hide();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmMaintenanceModal')).show();
    };
    document.getElementById('confirmMaintenance').onclick = () => { if (form.dataset.submitting) return; form.dataset.submitting = '1'; form.submit() };
};

initAssetMaintenances();
document.addEventListener('turbo:load', initAssetMaintenances);
