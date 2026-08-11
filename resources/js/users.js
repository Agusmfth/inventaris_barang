const initUsers = () => {
    const editButtons = document.querySelectorAll('.edit-user');
    const passwordButtons = document.querySelectorAll('.password-user');
    if (editButtons.length === 0 && passwordButtons.length === 0) return;

    const container = document.querySelector('.category-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    editButtons.forEach(button => button.addEventListener('click', () => {
        const form = document.getElementById('editUserForm');
        if (!form) return;
        form.action = button.dataset.url;
        form.elements.name.value = button.dataset.name;
        form.elements.username.value = button.dataset.username;
        form.elements.email.value = button.dataset.email;
        form.elements.role.value = button.dataset.role;
        form.elements.is_active.value = button.dataset.active;
    }));

    passwordButtons.forEach(button => button.addEventListener('click', () => {
        const form = document.getElementById('passwordUserForm');
        if (!form) return;
        form.action = button.dataset.url;
        document.getElementById('passwordUserName').textContent = `Tetapkan password baru untuk ${button.dataset.name}.`;
        form.reset();
    }));
};

initUsers();
document.addEventListener('turbo:load', initUsers);
