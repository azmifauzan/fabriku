import Swal from 'sweetalert2';

export function useSweetAlert() {
    const confirm = async (
        title: string,
        text: string,
        confirmButtonText: string = 'Ya',
        icon: 'warning' | 'question' | 'info' = 'question',
        confirmButtonColor: string = '#4f46e5',
    ) => {
        return await Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText,
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });
    };

    const confirmDelete = async (title: string, text: string) => {
        return await Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });
    };

    const showSuccess = (title: string, text?: string) => {
        return Swal.fire({
            title,
            text,
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true,
        });
    };

    const showError = (title: string, text?: string) => {
        return Swal.fire({
            title,
            text,
            icon: 'error',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK',
        });
    };

    const showWarning = (title: string, text?: string) => {
        return Swal.fire({
            title,
            text,
            icon: 'warning',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'OK',
        });
    };

    return {
        confirm,
        confirmDelete,
        showSuccess,
        showError,
        showWarning,
    };
}
