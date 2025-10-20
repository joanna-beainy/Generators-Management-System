<div class="container mt-4" dir="rtl" x-data="{ showSuccessAlert: true, showErrorAlert: true }">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-cash me-2"></i>
                        إدخال دفعة جديدة
                    </h5>
                    <small class="opacity-75">سعر الصرف: 1$ = {{ number_format($this->exchangeRate) }} ل.ل</small>
                </div>
                <a href="{{ route('users.dashboard') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-house me-1"></i>
                    إغلاق
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Success Message -->
            @if ($successMessage)
                <div class="alert alert-success alert-dismissible fade show" 
                     role="alert"
                     x-data="{ show: true }" 
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ $successMessage }}
                    <button type="button" class="btn-close" wire:click="$set('successMessage', null)"></button>
                </div>
            @endif

            <!-- Error Message -->
            @if ($errorMessage)
                <div class="alert alert-danger alert-dismissible fade show" 
                     role="alert"
                     x-data="{ show: true }" 
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $errorMessage }}
                    <button type="button" class="btn-close" wire:click="$set('errorMessage', null)"></button>
                </div>
            @endif

            <!-- Overpayment Confirmation Modal -->
            @if($showConfirmationModal)
                @include('livewire.partials.overpayment-confirmation-modal')
            @endif

            <!-- Search and Client Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">ابحث عن المشترك</label>
                    <div class="input-group">
                        <input type="text" 
                               wire:model="search" 
                               wire:keydown.enter="handleSearch"
                               class="form-control" 
                               placeholder="اكتب اسم المشترك أو رقمه..."
                               wire:loading.attr="disabled"
                               style="text-align: right;">
                        <button class="btn btn-outline-secondary" type="button" wire:click="handleSearch">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div wire:loading wire:target="handleSearch" class="small text-muted mt-1">
                        <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">المشترك المحدد</label>
                    <select wire:model.live="selectedClientId" class="form-select" style="text-align: right;">
                        <option value="">-- اختر المشترك --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                                {{ $client->id }} - {{ $client->full_name }} 
                            </option>
                        @endforeach
                    </select>
                    @error('selectedClientId')
                        <div class="text-danger small mt-1">{{ $message }}</div>
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
                    <div class="border rounded p-4 bg-light" wire:key="payment-form-{{ $selectedClient->id }}">
                        <h6 class="mb-3">
                            <i class="bi bi-person me-2"></i>
                            بيانات الدفعة للمشترك: <span class="text-primary">{{ $selectedClient->full_name }}</span>
                        </h6>
                        
                        <!-- Current Balance -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="alert alert-info border-0 mb-0">
                                    <strong>المبلغ المتبقي الحالي:</strong>
                                    <div class="h5 mb-0 text-dark">
                                        {{ number_format($selectedClient->current_remaining_usd, 2) }} $
                                    </div>
                                    <small class="text-muted">
                                        ≈ {{ number_format($selectedClient->current_remaining_lbp, 0) }} ل.ل
                                    </small>
                                </div>
                            </div>
                        </div>

                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Amount Paid -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">المبلغ المدفوع ($) *</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               wire:model.live="amount" 
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               step="0.01" 
                                               min="0.01"
                                               placeholder="0.00"
                                               required
                                               style="text-align: left;">
                                        <span class="input-group-text">$</span>
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
                                               class="form-control" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0.00"
                                               style="text-align: left;">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            ≈ {{ $this->discount_lbp }} ل.ل
                                        </small>
                                    </div>
                                </div>

                                <!-- Total Summary -->
                                <div class="col-12">
                                    <div class="alert alert-warning border-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>الإجمالي:</strong> 
                                            <span class="fw-bold text-dark h5 mb-0">
                                                {{ number_format($this->total_usd, 2) }} $
                                            </span>
                                        </div>
                                        <hr class="my-2">
                                        <small class="text-muted">
                                            ≈ {{ $this->total_lbp }} ل.ل
                                            - سيتم خصم هذا المبلغ من الرصيد المتبقي للمشترك
                                        </small>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 text-start">
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <i class="bi bi-save me-2"></i>
                                        ادخال الدفعة
                                        <span wire:loading wire:target="save">
                                            <i class="bi bi-arrow-repeat spinner fa-spin ms-2"></i>
                                        </span>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-secondary me-2"
                                            wire:click="$set('selectedClientId', null)"
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
                        <div class="alert alert-warning border-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            لا توجد نتائج للبحث "{{ $search }}"
                        </div>
                    @elseif(!$search && $clients->isEmpty())
                        <div class="alert alert-info border-0">
                            <i class="bi bi-check-circle me-2"></i>
                            لا يوجد مشتركين عاديون مسجلون في النظام
                        </div>
                    @else
                        <div class="text-muted">
                            <p class="h5">ابحث عن المشترك أو اختره من القائمة</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Include Receipt Modal Component -->
    @livewire('receipt-modal')
</div>