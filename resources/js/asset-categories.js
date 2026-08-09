document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.edit-category').forEach(button => button.addEventListener('click', () => {
        const category = JSON.parse(button.dataset.category);
        const form = document.getElementById('editCategoryForm');
        form.action = button.dataset.updateUrl;
        form.querySelector('[name="_category_id"]').value = category.id;
        form.querySelector('[name="name"]').value = category.name;
        form.querySelector('[name="code"]').value = category.code ?? '';
        form.querySelector('[name="description"]').value = category.description ?? '';
        form.querySelector('[name="is_active"]').value = category.is_active ? '1' : '0';
    }));

    document.querySelectorAll('.delete-category').forEach(button => button.addEventListener('click', () => {
        document.getElementById('deleteCategoryForm').action = button.dataset.deleteUrl;
        document.getElementById('deleteCategoryName').textContent = button.dataset.categoryName;
    }));

    const state = window.categoryFormState;
    const createModal = document.getElementById('createCategoryModal'), editModal = document.getElementById('editCategoryModal');
    if (state?.mode === 'create' && createModal) bootstrap.Modal.getOrCreateInstance(createModal).show();
    if (state?.mode === 'edit' && state.categoryId && editModal) bootstrap.Modal.getOrCreateInstance(editModal).show();
});
