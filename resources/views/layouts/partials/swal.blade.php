<script>
    window.swalBase = {
        confirmButtonColor: '#14130F',
        cancelButtonColor: '#E6E2D8',
        background: '#ffffff',
        color: '#14130F',
    };
    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        background: '#ffffff',
        color: '#14130F',
    });

    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success'))
            Toast.fire({ icon: 'success', title: @json(session('success')) });
        @endif
        @if(session('error'))
            Swal.fire({ ...swalBase, icon: 'error', title: 'Gagal', text: @json(session('error')) });
        @endif
    });

    // Generic confirm dialog: <form data-confirm="Pesan" data-confirm-title="Judul" data-confirm-button="Ya" data-confirm-type="primary">
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.dataset || !form.dataset.confirm || form.dataset.confirmed === '1') return;
        e.preventDefault();
        const danger = form.dataset.confirmType !== 'primary';
        Swal.fire({
            ...swalBase,
            icon: danger ? 'warning' : 'question',
            title: form.dataset.confirmTitle || 'Apakah Anda yakin?',
            text: form.dataset.confirm,
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: danger ? '#DC2626' : '#14130F',
            cancelButtonColor: '#E6E2D8',
            reverseButtons: true,
        }).then(r => {
            if (r.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    }, true);
</script>
