<div>
    <div class="min-vh-100 d-flex justify-content-center align-items-center bg-light px-3">
        <div class="card shadow border-0" style="width: 550px; border-top: 5px solid #0d6efd !important; border-radius: 12px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="display-6 mb-2">🔐</div>
                    <h2 class="fw-bold text-dark h4 mb-1">System Activation</h2>
                    <p class="text-muted small">This application is locked to this hardware. Please provide a valid license to continue.</p>
                </div>

                <hr class="mb-4 opacity-10">

                <!-- Step 1: Identification -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary small text-uppercase tracking-wider">Step 1: Copy your Machine ID</label>
                    <div class="input-group">
                        <input type="text" id="machineIdInput" class="form-control form-control-lg bg-light border-0 text-center font-monospace" value="{{ $machineId }}" readonly style="font-size: 0.85rem;">
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText('{{ $machineId }}'); this.innerHTML='<i class=\'bi bi-check\'></i>'; setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i>', 2000)">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div class="form-text mt-2">Send this code to your administrator to receive your <code>.lic</code> file.</div>
                </div>

                <!-- Step 2: Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary small text-uppercase tracking-wider">Step 2: Upload License File</label>
                    <div class="input-group">
                        <input type="file" class="form-control" wire:model="licenseFile" accept=".lic" id="licenseFileInput">
                    </div>
                    <div class="form-text mt-2">Maximum file size: 10KB. Only <code>.lic</code> files are accepted.</div>
                </div>

                @if($error)
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>{{ $error }}</div>
                    </div>
                @endif

                <div class="d-grid mt-5">
                    <button wire:click="activate" class="btn btn-primary btn-lg fw-bold py-3 shadow-sm" style="border-radius: 10px;">
                        <span wire:loading.remove wire:target="licenseFile, activate">Activate Application</span>
                        <span wire:loading wire:target="licenseFile, activate">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Processing...
                        </span>
                    </button>
                </div>

                <div class="text-center mt-4 text-muted small">
                    Generators Management System &copy; {{ date('Y') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .tracking-wider { letter-spacing: 0.05em; }
        #machineIdInput:focus { box-shadow: none; }
        .btn-primary { transition: transform 0.2s; }
        .btn-primary:active { transform: scale(0.98); }
    </style>
</div>
