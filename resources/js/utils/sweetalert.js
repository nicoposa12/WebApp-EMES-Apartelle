import Swal from 'sweetalert2';

// Create a custom-styled SweetAlert instance to match the premium theme
const PremiumToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

const PremiumSwal = Swal.mixin({
    customClass: {
        container: 'premium-swal-container',
        popup: 'premium-swal-popup rounded-4 border-0',
        title: 'serif-font fw-bold text-secondary-dark',
        confirmButton: 'btn btn-gold rounded-pill px-4 py-2 mx-2 fw-bold text-uppercase',
        cancelButton: 'btn btn-light rounded-pill px-4 py-2 mx-2 fw-bold text-uppercase',
        denyButton: 'btn btn-danger rounded-pill px-4 py-2 mx-2 fw-bold text-uppercase',
        actions: 'premium-swal-actions'
    },
    buttonsStyling: false,
    background: '#FDFBF7'
});

export const notify = {
    success(title, message = '') {
        PremiumToast.fire({
            icon: 'success',
            title: title,
            text: message,
            iconColor: '#BC9151'
        });
    },
    error(title, message = '') {
        PremiumToast.fire({
            icon: 'error',
            title: title,
            text: message
        });
    },
    info(title, message = '') {
        PremiumToast.fire({
            icon: 'info',
            title: title,
            text: message
        });
    }
};

export const confirm = async (options = {}) => {
    return await PremiumSwal.fire({
        title: options.title || 'Are you sure?',
        text: options.text || 'You won\'t be able to revert this!',
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: options.confirmText || 'Yes, proceed',
        cancelButtonText: options.cancelText || 'Cancel',
        iconColor: options.icon === 'warning' ? '#BC9151' : undefined,
        ...options
    });
};

export default PremiumSwal;
