document.addEventListener('DOMContentLoaded', () => {
    const price = document.getElementById('acquisition_price');
    const photo = document.getElementById('photo');
    const preview = document.getElementById('photoPreview');
    const form = document.getElementById('assetForm');
    const submit = document.getElementById('assetSubmit');
    if (!form || !price || !submit) return;
    const formatPrice = () => { const value = price.value.replace(/\D/g, ''); price.value = value ? new Intl.NumberFormat('id-ID').format(Number(value)) : '0'; };
    price?.addEventListener('input', formatPrice); formatPrice();
    photo?.addEventListener('change', () => {
        const file = photo.files[0]; if (!file) return;
        const reader = new FileReader(); reader.onload = e => { preview.innerHTML = `<img src="${e.target.result}" alt="Preview foto aset">`; }; reader.readAsDataURL(file);
    });
    form?.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true') { event.preventDefault(); return; }
        form.dataset.submitting = 'true'; price.value = price.value.replace(/\D/g, ''); submit.disabled = true; submit.classList.add('is-loading');
    });
});
