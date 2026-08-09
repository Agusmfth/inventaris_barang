document.addEventListener('DOMContentLoaded', () => {
    const asset = document.getElementById('mutationAsset');
    const search = document.getElementById('assetSearch');
    const destination = document.getElementById('toLocation');
    const date = document.getElementById('mutationDate');
    const current = document.querySelector('#currentLocation span');
    const form = document.getElementById('mutationForm');
    const review = document.getElementById('reviewMutation');
    const confirm = document.getElementById('confirmMutation');
    if (!form) return;

    const initialOption = asset.querySelector('option[selected]');
    if (!initialOption) asset.selectedIndex = -1;
    asset.classList.add('mutation-native-select');
    asset.required = false;
    search.required = true;
    search.autocomplete = 'off';
    search.setAttribute('role', 'combobox');
    search.setAttribute('aria-autocomplete', 'list');
    search.setAttribute('aria-expanded', 'false');
    destination.required = true;
    date.required = true;

    const results = document.createElement('div');
    results.className = 'mutation-combobox-results';
    results.setAttribute('role', 'listbox');
    search.insertAdjacentElement('afterend', results);

    const syncLocation = () => {
        const selected = asset.options[asset.selectedIndex];
        current.textContent = selected?.dataset.location || 'Pilih aset terlebih dahulu';
        [...destination.options].forEach(option => option.disabled = option.value && option.value === selected?.dataset.locationId);
        if (destination.value === selected?.dataset.locationId) destination.value = '';
    };

    const closeResults = () => {
        results.classList.remove('show');
        search.setAttribute('aria-expanded', 'false');
    };

    const choose = option => {
        asset.value = option.value;
        search.value = option.text.trim();
        search.setCustomValidity('');
        syncLocation();
        closeResults();
    };

    const renderResults = () => {
        const needle = search.value.toLowerCase().trim();
        const options = [...asset.options].filter(option => !needle || option.text.toLowerCase().includes(needle));
        results.innerHTML = '';
        options.slice(0, 8).forEach(option => {
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('role', 'option');
            button.className = 'mutation-combobox-option';
            const [code, ...name] = option.text.split('—');
            button.innerHTML = `<b>${code.trim()}</b><span>${name.join('—').trim()}</span><small>${option.dataset.location}</small>`;
            button.addEventListener('mousedown', event => { event.preventDefault(); choose(option); });
            results.appendChild(button);
        });
        if (!options.length) results.innerHTML = '<div class="mutation-combobox-empty">Aset tidak ditemukan.</div>';
        results.classList.add('show');
        search.setAttribute('aria-expanded', 'true');
    };

    search.addEventListener('focus', renderResults);
    search.addEventListener('input', () => { asset.selectedIndex = -1; syncLocation(); renderResults(); });
    search.addEventListener('blur', () => window.setTimeout(closeResults, 120));
    if (initialOption) choose(initialOption); else syncLocation();

    review.addEventListener('click', () => {
        if (!asset.value) search.setCustomValidity('Pilih aset dari daftar hasil pencarian.');
        if (!form.reportValidity()) return;
        const selected = asset.options[asset.selectedIndex];
        const target = destination.options[destination.selectedIndex];
        document.getElementById('confirmAsset').textContent = selected.text;
        document.getElementById('confirmFrom').textContent = selected.dataset.location;
        document.getElementById('confirmTo').textContent = target.text;
        document.getElementById('confirmDate').textContent = new Intl.DateTimeFormat('id-ID', {dateStyle:'long'}).format(new Date(date.value+'T00:00:00'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mutationModal')).hide();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmMutationModal')).show();
    });
    confirm.addEventListener('click', () => {
        if (form.dataset.submitting) return;
        form.dataset.submitting = 'true';
        confirm.disabled = true;
        confirm.classList.add('is-loading');
        form.submit();
    });
    if (window.mutationHasErrors) bootstrap.Modal.getOrCreateInstance(document.getElementById('mutationModal')).show();
});
