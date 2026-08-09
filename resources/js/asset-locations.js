document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.edit-location').forEach(button => button.addEventListener('click', () => {
        const location = JSON.parse(button.dataset.location);
        const form = document.getElementById('editLocationForm');
        form.action = button.dataset.updateUrl;
        form.querySelector('[name="_location_id"]').value = location.id;
        form.querySelector('[name="name"]').value = location.name;
        form.querySelector('[name="code"]').value = location.code;
        form.querySelector('[name="person_in_charge"]').value = location.person_in_charge ?? '';
        form.querySelector('[name="description"]').value = location.description ?? '';
        form.querySelector('[name="is_active"]').value = location.is_active ? '1' : '0';
    }));
    document.querySelectorAll('.delete-location').forEach(button => button.addEventListener('click', () => {
        document.getElementById('deleteLocationForm').action = button.dataset.deleteUrl;
        document.getElementById('deleteLocationName').textContent = button.dataset.locationName;
    }));
    const state = window.locationFormState;
    const createModal = document.getElementById('createLocationModal'), editModal = document.getElementById('editLocationModal');
    if (state?.mode === 'create' && createModal) bootstrap.Modal.getOrCreateInstance(createModal).show();
    if (state?.mode === 'edit' && state.locationId && editModal) bootstrap.Modal.getOrCreateInstance(editModal).show();
});
