<div class="container mt-2" dir="rtl">
    
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-tools text-success me-2"></i> إدخال مصاريف صيانة
            </h3>
            <p class="text-secondary mb-0 mt-1">
                <i class="bi bi-info-circle me-1"></i> تسجيل عملية صيانة جديدة لمشترك محدد
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    {{-- Alpine.js Auto-Disappearing Alert --}}
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

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4">
            
            <!-- Search and Client Selection -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-search me-1"></i> ابحث عن المشترك</label>
                    <div class="input-group overflow-hidden rounded-pill shadow-sm border">
                         <input type="text" 
                               wire:model="search" 
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
                    <div wire:loading wire:target="handleSearch" class="small text-success mt-2 ms-2">
                        <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-person-check me-1"></i> اختيار مشترك</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedClientId" x-ref="clientDropdown" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            <option value="">-- اختر المشترك --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                                    {{ $client->id }} - {{ $client->full_name }} 
                                </option>
                            @endforeach
                        </select>
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
                                        class="btn btn-outline-secondary rounded-pill px-4 ms-2"
                                        wire:click="$set('selectedClientId', null)"
                                        @click="$refs.searchField.value = ''; $refs.clientDropdown.value = ''; $refs.searchField.focus()"
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