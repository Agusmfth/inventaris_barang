@if(session('success'))
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="alert alert-success alert-dismissible shadow-sm auth-success" role="alert" data-auto-dismiss><i data-lucide="circle-check" class="me-2" style="width:18px"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
</div>
@endif
@if(session('error'))
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="alert alert-danger alert-dismissible shadow-sm" role="alert" data-auto-dismiss><i data-lucide="circle-alert" class="me-2" style="width:18px"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
</div>
@endif
