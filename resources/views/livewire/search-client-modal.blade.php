<div>
    @if ($show)
    <div class="modal fade show d-block" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-m modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-light text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-search text-success me-2"></i>
                        بحث عن مشترك
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" wire:click="closeModal"></button>
                </div>

                <div class="modal-body">
                    {{-- Auto-Disappearing Alert --}}
                    @if ($alertMessage)
                        <div 
                            x-data="{ show: true }" 
                            x-show="show" 
                            x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="alert alert-{{ $alertType }} alert-dismissible fade show text-center rounded-3 shadow-sm mb-4">
                            <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                            {{ $alertMessage }}
                            <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                        </div>
                    @endif

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

