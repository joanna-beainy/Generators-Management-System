<div>
    @if ($show)
    <div class="modal fade show d-block" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-m modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-search me-2"></i>
                        بحث عن مشترك
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>

                <div class="modal-body">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">ابحث عن المشترك</label>
                        <div class="input-group">
                            <input type="text" 
                                wire:model.live.debounce.500ms="search" 
                                class="form-control" 
                                placeholder="اكتب اسم المشترك أو رقمه..."
                                style="text-align: right;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                        </div>
                    </div>

                    <!-- Client List -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر المشترك</label>
                        <select wire:model.live="selectedClientId" class="form-select" style="text-align: right;">
                            <option value="">-- اختر المشترك --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->id }} - {{ $client->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errorMessage)
                        <div class="alert alert-danger">
                            {{ $errorMessage }}
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeModal">إغلاق</button>
                    <button class="btn btn-success" wire:click="handleSelection">
                        متابعة
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

