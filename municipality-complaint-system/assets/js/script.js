// Preview uploaded image before submit
const imgInput = document.getElementById('complaint_image');
if (imgInput) {
    imgInput.addEventListener('change', function () {
        const preview = document.getElementById('image-preview');
        if (!preview) return;
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.classList.add('d-none');
        }
    });
}

// Confirm before destructive actions
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
        if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
});

// Auto-dismiss alerts after 4 s
setTimeout(() => {
    document.querySelectorAll('.alert.fade').forEach(a => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
        bsAlert.close();
    });
}, 4000);
