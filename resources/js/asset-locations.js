const initAssetLocations = () => {
    const editButtons = document.querySelectorAll('.edit-location');
    const deleteButtons = document.querySelectorAll('.delete-location');
    if (editButtons.length === 0 && deleteButtons.length === 0) return;

    const container = document.querySelector('.category-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    editButtons.forEach(button => button.addEventListener('click', () => {
        const location = JSON.parse(button.dataset.location);
        const form = document.getElementById('editLocationForm');
        if (!form) return;
        form.action = button.dataset.updateUrl;
        form.querySelector('[name="_location_id"]').value = location.id;
        form.querySelector('[name="name"]').value = location.name;
        form.querySelector('[name="code"]').value = location.code;
        form.querySelector('[name="person_in_charge"]').value = location.person_in_charge ?? '';
        form.querySelector('[name="description"]').value = location.description ?? '';
        form.querySelector('[name="is_active"]').value = location.is_active ? '1' : '0';
    }));
    deleteButtons.forEach(button => button.addEventListener('click', () => {
        const form = document.getElementById('deleteLocationForm');
        if (!form) return;
        form.action = button.dataset.deleteUrl;
        document.getElementById('deleteLocationName').textContent = button.dataset.locationName;
    }));
    const state = window.locationFormState;
    const createModal = document.getElementById('createLocationModal'), editModal = document.getElementById('editLocationModal');
    if (state?.mode === 'create' && createModal) bootstrap.Modal.getOrCreateInstance(createModal).show();
    if (state?.mode === 'edit' && state.locationId && editModal) bootstrap.Modal.getOrCreateInstance(editModal).show();
};

initAssetLocations();
document.addEventListener('turbo:load', initAssetLocations);
