const initAssetForm = () => {
    const container = document.querySelector('.asset-form-page');
    if (!container || container.dataset.initialized === '1') return;
    container.dataset.initialized = '1';

    const price = document.getElementById('acquisition_price');
    const photo = document.getElementById('photo');
    const preview = document.getElementById('photoPreview');
    const form = document.getElementById('assetForm');
    const submit = document.getElementById('assetSubmit');
    if (!form || !price || !submit) return;

    const formatPrice = () => {
        const value = price.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        price.value = value ? value.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '0';
    };

    price?.addEventListener('input', formatPrice);
    formatPrice();

    photo?.addEventListener('change', () => {
        const file = photo.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview foto aset">`;
        };
        reader.readAsDataURL(file);
    });

    form?.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        price.value = price.value.replace(/\D/g, '');
        submit.disabled = true;
        submit.classList.add('is-loading');
    });
};

initAssetForm();
document.addEventListener('turbo:load', initAssetForm);
