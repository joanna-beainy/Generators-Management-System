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
                    @if ($alertMessage)
                        <div
                            x-data="{ show: true }"
                            x-show="show"
                            x-init="setTimeout(() => { show = false, $wire.set('alertMessage', null) }, 5000)"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">
                            <div class="alert alert-{{ $alertType }} border-0 text-center rounded-3 shadow-sm mb-4 position-relative">
                                <button type="button" class="btn-close position-absolute top-50 translate-middle-y" style="right: 1rem;" wire:click="$set('alertMessage', null)"></button>
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                                    {{ $alertMessage }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3" x-data x-on:focus-client-search.window="$nextTick(() => $refs.clientSearch && $refs.clientSearch.focus())">
                        <label class="form-label fw-bold medium">
                            <i class="bi bi-person-badge me-1"></i> ابحث عن مشترك
                        </label>
                        <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text"
                                x-ref="clientSearch"
                                autofocus
                                wire:model.live.debounce.300ms="search"
                                class="form-control border-0 py-2"
                                placeholder="اكتب اسم المشترك أو رقمه..."
                                style="text-align: right; box-shadow: none;">
                        </div>

                        @if($showSearchResults && $search)
                            <div class="list-group w-100 shadow-sm border rounded-3 mt-1 overflow-auto bg-white" style="max-height: 260px;">
                                @forelse($clients as $client)
                                    <button type="button"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                            wire:click="selectClient({{ $client->id }})">
                                        <span>{{ $client->id }} - {{ $client->full_name }}</span>
                                        <i class="bi bi-person text-muted"></i>
                                    </button>
                                @empty
                                    <div class="list-group-item text-muted small">
                                        لا توجد نتائج مطابقة
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    @if(!$showSearchResults && $selectedClientId)
                        @php
                            $selectedClient = $clients->firstWhere('id', (int) $selectedClientId);
                        @endphp
                        @if($selectedClient)
                            <div class="alert alert-light border rounded-3 py-2 px-3 mb-0">
                                <i class="bi bi-person-check me-1 text-success"></i>
                                {{ $selectedClient->id }} - {{ $selectedClient->full_name }}
                            </div>
                        @endif
                    @endif
                </div>

                <div class="modal-footer border-0 p-4 bg-light bg-opacity-50 justify-content-between">
                    <button class="btn btn-outline-danger rounded-pill px-4" wire:click="closeModal">
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
