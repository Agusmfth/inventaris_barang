const initAssetCategories = () => {
    const editButtons = document.querySelectorAll('.edit-category');
    const deleteButtons = document.querySelectorAll('.delete-category');
    if (editButtons.length === 0 && deleteButtons.length === 0) return;

    const container = document.querySelector('.category-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    editButtons.forEach(button => button.addEventListener('click', () => {
        const category = JSON.parse(button.dataset.category);
        const form = document.getElementById('editCategoryForm');
        if (!form) return;
        form.action = button.dataset.updateUrl;
        form.querySelector('[name="_category_id"]').value = category.id;
        form.querySelector('[name="name"]').value = category.name;
        form.querySelector('[name="code"]').value = category.code ?? '';
        form.querySelector('[name="description"]').value = category.description ?? '';
        form.querySelector('[name="is_active"]').value = category.is_active ? '1' : '0';
    }));

    deleteButtons.forEach(button => button.addEventListener('click', () => {
        const form = document.getElementById('deleteCategoryForm');
        if (!form) return;
        form.action = button.dataset.deleteUrl;
        document.getElementById('deleteCategoryName').textContent = button.dataset.categoryName;
    }));

    const state = window.categoryFormState;
    const createModal = document.getElementById('createCategoryModal'), editModal = document.getElementById('editCategoryModal');
    if (state?.mode === 'create' && createModal) bootstrap.Modal.getOrCreateInstance(createModal).show();
    if (state?.mode === 'edit' && state.categoryId && editModal) bootstrap.Modal.getOrCreateInstance(editModal).show();
};

initAssetCategories();
document.addEventListener('turbo:load', initAssetCategories);
