const initFundingSources = () => {
    const editButtons = document.querySelectorAll('.edit-source');
    const deleteButtons = document.querySelectorAll('.delete-source');
    if (editButtons.length === 0 && deleteButtons.length === 0) return;

    const container = document.querySelector('.category-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    editButtons.forEach(button => button.addEventListener('click', () => {
        const source = JSON.parse(button.dataset.source), form = document.getElementById('editSourceForm');
        if (!form) return;
        form.action = button.dataset.updateUrl;
        form.querySelector('[name="_source_id"]').value = source.id;
        form.querySelector('[name="name"]').value = source.name;
        form.querySelector('[name="code"]').value = source.code;
        form.querySelector('[name="description"]').value = source.description ?? '';
        form.querySelector('[name="is_active"]').value = source.is_active ? '1' : '0';
    }));
    deleteButtons.forEach(button => button.addEventListener('click', () => {
        const form = document.getElementById('deleteSourceForm');
        if (!form) return;
        form.action = button.dataset.deleteUrl;
        document.getElementById('deleteSourceName').textContent = button.dataset.sourceName;
    }));
    const state = window.sourceFormState;
    const createModal = document.getElementById('createSourceModal'), editModal = document.getElementById('editSourceModal');
    if (state?.mode === 'create' && createModal) bootstrap.Modal.getOrCreateInstance(createModal).show();
    if (state?.mode === 'edit' && state.sourceId && editModal) bootstrap.Modal.getOrCreateInstance(editModal).show();
};

initFundingSources();
document.addEventListener('turbo:load', initFundingSources);
