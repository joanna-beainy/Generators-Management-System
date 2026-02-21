<div class="container d-flex flex-column" style="height: calc(100vh - 126px); overflow-y: auto;" dir="rtl" x-data="{}">
    <style>
        :root {
            --fluid-v-gap: clamp(0.5rem, 2vh, 1.5rem);
            --fluid-v-padding: clamp(0.75rem, 3vh, 2rem);
            --fluid-v-header-margin: clamp(0.5rem, 2vh, 1.5rem);
        }
    </style>
    
    <!-- Header Section -->
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center" style="margin-top: var(--fluid-v-header-margin); margin-bottom: var(--fluid-v-header-margin);">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-tools text-success me-2"></i> إدخال مصاريف صيانة
            </h3>
            <p class="text-secondary mb-0 mt-1 small">
                <i class="bi bi-info-circle me-1"></i> تسجيل عملية صيانة جديدة لمشترك محدد
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    {{-- Alpine.js Auto-Disappearing Alert --}}
    @if ($alertMessage)
        <div class="flex-shrink-0"
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

    <div class="flex-shrink-1 pb-5" style="min-height: 0;">
        <div class="row justify-content-center mx-0">
            <div class="col-lg-7 col-xl-6 px-0 px-md-3">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body bg-white" style="padding-top: var(--fluid-v-padding); padding-bottom: var(--fluid-v-padding); padding-left: 1.5rem; padding-right: 1.5rem;">
            
            <!-- Search and Client Selection -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-search me-1"></i> ابحث عن المشترك</label>
                    <div class="input-group overflow-hidden rounded-pill shadow-sm border">
                         <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               x-ref="searchField"
                               wire:keydown.enter="handleSearch"
                               class="form-control border-0" 
                               placeholder="اكتب اسم المشترك أو رقمه..."
                               autofocus
                               style="text-align: right; box-shadow: none;">
                        <button class="btn btn-white border-0" type="button" wire:click="handleSearch">
                            <i class="bi bi-search text-secondary"></i>
                        </button>
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
                                    لا توجد نتائج
                                </div>
                            @endforelse
                        </div>
                    @endif
                    <div wire:loading wire:target="handleSearch,search" class="small text-success mt-2 ms-2">
                        <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
                    </div>
                @error('selectedClientId')
                    <div class="text-danger small mt-1 ms-2">{{ $message }}</div>
                @enderror
                </div>
                
            </div>

            <!-- Maintenance Form -->
            @if($selectedClient)
                
                <!-- Show maintenance form for regular clients -->
                <div class="border-0 rounded-4 p-4 pb-2 bg-light shadow-sm" wire:key="maintenance-form-{{ $selectedClient->id }}">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-white p-3 rounded-circle shadow-sm me-3">
                            <i class="bi bi-person text-success h4 mb-0"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $selectedClient->full_name }}</h5>
                        </div>
                    </div>
                    
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">مبلغ الصيانة ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" 
                                           wire:model="amount" 
                                           x-init="$el.focus()"
                                           class="form-control border-0" 
                                           step="0.50" 
                                           min="0.50"
                                           placeholder="0.00"
                                           required
                                           style="text-align: right; box-shadow: shadow-sm;">
                                    <span class="input-group-text border-0 bg-white text-success fw-bold">$</span>
                                </div>
                                @error('amount')
                                    <div class="text-danger small mt-1 ms-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">وصف الصيانة (اختياري)</label>
                                <textarea 
                                    wire:model="description"
                                    class="form-control shadow-sm rounded-4 border-0"
                                    rows="3"
                                    placeholder="أدخل وصفًا للصيانة المنجزة..."
                                    style="text-align: right;"
                                ></textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 text-start mt-4">
                                <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm" wire:loading.attr="disabled">
                                    <i class="bi bi-check-circle me-2"></i>
                                    إدخال مصاريف الصيانة
                                    <span wire:loading wire:target="save">
                                        <i class="bi bi-arrow-repeat spinner fa-spin ms-2"></i>
                                    </span>
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-outline-danger rounded-pill px-4 ms-2"
                                        wire:click="resetFilters"
                                        @click="$refs.searchField.value = ''; $refs.searchField.focus()"
                                        wire:loading.attr="disabled">
                                    <i class="bi bi-x-circle me-1"></i> إلغاء
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <!-- No Client Selected State -->
                <div class="text-center py-5">
                    @if($search && $clients->isEmpty())
                        <div class="py-5">
                            <i class="bi bi-search display-1 text-muted opacity-25 mb-3"></i>
                            <h5 class="text-muted">لا توجد نتائج للبحث "{{ $search }}"</h5>
                        </div>
                    @elseif(!$search && $clients->isEmpty())
                        <div class="py-5">
                            <i class="bi bi-people display-1 text-muted opacity-25 mb-3"></i>
                            <h5 class="text-muted">لا يوجد مشتركين حتى الآن</h5>
                        </div>
                    @else
                       <div class="text-center py-5">
                            <i class="bi bi-person-gear display-1 text-success opacity-50 mb-3"></i>
                            <h5 class="text-muted">اختر مشتركاً لبدء إدخال الصيانة</h5>
                            <p class="text-muted small">يمكنك البحث بالاسم أو الرقم</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
            </div>
        </div>
    </div>
</div>
