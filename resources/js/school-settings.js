const initSchoolSettings = () => {
    const container = document.querySelector('.school-setting-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    const logo = document.getElementById('logo');
    const preview = document.getElementById('schoolLogoPreview');
    const miniLogo = document.getElementById('miniLogo');
    const name = document.getElementById('school_name');
    const title = document.getElementById('inventory_label_title');
    const mark = document.getElementById('inventory_label_mark');
    const form = document.getElementById('schoolSettingForm');
    const submit = document.getElementById('settingSubmit');

    name?.addEventListener('input', () => document.getElementById('miniSchoolName').textContent = name.value || 'Nama Sekolah');
    title?.addEventListener('input', () => document.getElementById('miniLabelTitle').textContent = title.value || 'BARANG INVENTARIS');
    mark?.addEventListener('input', () => document.getElementById('miniLabelMark').textContent = mark.value || 'INVENTARIS SEKOLAH');
    logo?.addEventListener('change', () => {
        const file = logo.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview logo sekolah">`;
            miniLogo.innerHTML = `<img src="${e.target.result}" alt="">`;
        };
        reader.readAsDataURL(file);
    });
    form?.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        submit.disabled = true;
        submit.classList.add('is-loading');
    });
};

initSchoolSettings();
document.addEventListener('turbo:load', initSchoolSettings);
