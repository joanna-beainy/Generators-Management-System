<div class="container mt-2" dir="rtl" x-data="{}">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-wallet2 text-success me-2"></i> إدخال دفعة جديدة
            </h3>
            <p class="text-secondary mb-0 mt-1">
                <i class="bi bi-currency-exchange me-1"></i> سعر الصرف: 1$ = <span class="fw-bold text-dark">{{ number_format($this->exchangeRate) }} ل.ل</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <!-- Alpine.js Auto-Disappearing Alert -->
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
            <!-- Overpayment Confirmation Modal -->
            @if($showConfirmationModal)
                @include('livewire.partials.overpayment-confirmation-modal')
            @endif

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
                            
                        </button>
                    </div>
                    <div wire:loading wire:target="handleSearch" class="small text-muted mt-1 px-2">
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
                        <div class="text-danger small mt-1 px-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Payment Form -->
            @if($selectedClient)
                @php
                    $selectedClientObj = $this->getSelectedClient();
                @endphp
                
                @if($selectedClientObj && $selectedClientObj->is_offered)
                    <!-- Show message for offered clients -->
                    <div class="border rounded p-4 bg-warning bg-opacity-10">
                        <div class="text-center py-4">
                            <i class="bi bi-gift fa-2x text-warning mb-3"></i>
                            <h5 class="text-warning">المشترك مقدم كتقدمة</h5>
                            <p class="text-muted mb-0">
                                لا يمكن إدخال دفعات أو طباعة إيصالات للمشتركين المقدمين كتقدمة.
                            </p>
                        </div>
                    </div>
                @else
                    <!-- Show payment form for regular clients -->
                    <div class="border-0 rounded-4 p-4 pb-2 bg-light shadow-sm" wire:key="payment-form-{{ $selectedClient->id }}">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-white p-3 rounded-circle shadow-sm me-3">
                                <i class="bi bi-person text-success h4 mb-0"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $selectedClient->full_name }}</h5>
                            </div>
                        </div>
                        
                        <!-- Current Balance -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                                    <div class="card-body p-3">
                                        <div class="text-muted small mb-1">المبلغ المتبقي الحالي</div>
                                        <div class="h4 fw-bold mb-0 text-primary">
                                            {{ number_format($selectedClient->current_remaining_usd, 2) }} $
                                        </div>
                                        <div class="small text-secondary fw-semibold mt-1">
                                            ≈ {{ number_format($selectedClient->current_remaining_lbp, 0) }} ل.ل
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Amount Paid -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">المبلغ المدفوع ($) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               wire:model.live="amount" 
                                               x-init="$el.focus()"
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               step="0.50" 
                                               min="0.50"
                                               placeholder="0.00"
                                               required
                                               style="text-align: left; box-shadow: shadow-sm;">
                                        <span class="input-group-text border-0 bg-white text-success fw-bold">$</span>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            ≈ {{ $this->amount_lbp }} ل.ل
                                        </small>
                                    </div>
                                </div>

                                <!-- Discount -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">الخصم ($) (اختياري)</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               wire:model.live="discount" 
                                               class="form-control @error('discount') is-invalid @enderror" 
                                               step="0.50" 
                                               min="0"
                                               placeholder="0.00"
                                               style="text-align: left;">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    @error('discount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            ≈ {{ $this->discount_lbp }} ل.ل
                                        </small>
                                    </div>
                                </div>

                                <!-- Total Summary -->
                                <div class="col-12 mt-4">
                                    <div class="card border-0 shadow-sm rounded-4 bg-success bg-opacity-10">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-start align-items-center gap-3">
                                                <span class="fw-bold text-success h5 mb-0">إجمالي الدفعة:</span> 
                                                <div class="text-end">
                                                    <div class="h4 fw-bold text-dark mb-0">
                                                        {{ number_format($this->total_usd, 2) }} $
                                                    </div>
                                                    <div class="small text-secondary fw-semibold">
                                                        ≈ {{ $this->total_lbp }} ل.ل
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 text-start mt-4">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm" wire:loading.attr="disabled">
                                        <i class="bi bi-save me-2"></i>
                                        ادخال الدفعة
                                        <span wire:loading wire:target="save">
                                            <i class="bi bi-arrow-repeat spinner fa-spin ms-2"></i>
                                        </span>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-secondary rounded-pill px-4 ms-2"
                                            wire:click="resetFilters"
                                            @click="$refs.searchField.value = ''; $refs.clientDropdown.value = ''; $refs.searchField.focus()"
                                            wire:loading.attr="disabled">
                                        إلغاء
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
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
                            <i class="bi bi-wallet2 display-1 text-success opacity-50 mb-3"></i>
                            <h5 class="text-muted">اختر مشتركاً لبدء إدخال الدفعة</h5>
                            <p class="text-muted small">يمكنك البحث بالاسم أو الرقم</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Include Receipt Modal Component -->
    @livewire('receipt-modal')
</div>