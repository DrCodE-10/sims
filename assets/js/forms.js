/**
 * SIMS - Client-side form validation
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            const required = form.querySelectorAll('[required]');
            let valid = true;
            required.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('input-error');
                } else {
                    input.classList.remove('input-error');
                }
            });
            if (!valid) {
                e.preventDefault();
                if (window.SIMS && window.SIMS.showToast) {
                    window.SIMS.showToast('Please fill in all required fields.', 'error');
                }
            }
        });
    });

    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', () => {
            const v = input.value.trim();
            if (v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                input.setCustomValidity('Please enter a valid email.');
            } else {
                input.setCustomValidity('');
            }
        });
    });
});
