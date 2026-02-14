<div>
    @if ($show)
    <div class="modal fade show d-block" tabindex="-1" wire:ignore.self style="background-color: rgba(0,0,0,0.4); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered" dir="rtl">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 pb-3">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="bi bi-search me-2"></i>
                        بحث عن مشترك
                    </h5>
                    <button type="button" class="btn-close shadow-none" wire:click="closeModal"></button>
                </div>

                <div class="modal-body p-4">
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

                    <!-- Search Input -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small">
                            <i class="bi bi-person-badge me-1"></i> ابحث عن مشترك
                        </label>
                        <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                wire:model.live.debounce.500ms="search" 
                                x-data 
                                x-init="$nextTick(() => $el.focus())"
                                class="form-control border-0 py-2" 
                                placeholder="اكتب اسم المشترك أو رقمه..."
                                style="text-align: right; box-shadow: none;">
                        </div>
                    </div>

                    <!-- Client List -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">
                            <i class="bi bi-list-check me-1"></i> اختيار مشترك
                        </label>
                        <div class="shadow-sm rounded-pill overflow-hidden border bg-white">
                            <select wire:model.live="selectedClientId" class="form-select border-0 py-2" style="text-align: right; box-shadow: none;">
                                <option value="">-- اختر المشترك من القائمة --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->id }} - {{ $client->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(empty($clients) && strlen($search) > 0)
                            <div class="text-center mt-2 text-muted small">
                                لا توجد نتائج مطابقة
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 bg-light bg-opacity-50 justify-content-between">
                    <button class="btn btn-outline-secondary rounded-pill px-4" wire:click="closeModal">
                        <i class="bi bi-x me-1"></i> إغلاق
                    </button>
                    <button class="btn btn-success rounded-pill px-5 shadow-sm" wire:click="handleSelection">
                        <i class="bi bi-arrow-left me-2"></i> متابعة
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
