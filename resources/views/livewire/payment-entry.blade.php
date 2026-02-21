<div class="container d-flex flex-column h-100" style="overflow-y: auto;" dir="rtl" x-data="{}" x-on:clear-payment-entry-search.window="$refs.searchField.value = ''; $refs.searchField.focus()">
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
                <i class="bi bi-wallet2 text-success me-2"></i> إدخال دفعة جديدة
            </h3>
            <p class="mb-0 mt-1 small">
                <i class="bi bi-currency-exchange me-1"></i> سعر الصرف: 1$ = <span class="fw-bold">{{ number_format($this->exchangeRate) }} ل.ل</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    @if ($alertMessage)
        <div class="flex-shrink-0"
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)"
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

    <div class="flex-shrink-1 pb-5" style="min-height: 0;">
        <div class="row justify-content-center mx-0">
            <div class="col-lg-7 col-xl-6 px-0 px-md-3">

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body bg-white" style="padding-top: var(--fluid-v-padding); padding-bottom: var(--fluid-v-padding); padding-left: 1.5rem; padding-right: 1.5rem;">
                        <!-- Overpayment Confirmation Modal -->
                        @if($showConfirmationModal)
                            @include('livewire.partials.overpayment-confirmation-modal')
                        @endif

                        <!-- Search and Client Selection -->
                        <div class="row g-3" style="margin-bottom: var(--fluid-v-gap);">
                            <div class="col-12">
                                <label class="form-label fw-bold"><i class="bi bi-search me-1"></i> ابحث عن المشترك</label>
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

                                <div wire:loading wire:target="handleSearch,search" class="small text-muted mt-1 px-2">
                                    <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
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
                                <div class="border rounded bg-warning bg-opacity-10" style="padding-top: var(--fluid-v-padding); padding-bottom: var(--fluid-v-padding); padding-left: 1rem; padding-right: 1rem;">
                                    <div class="text-center py-2">
                                        <i class="bi bi-gift h1 text-warning mb-2"></i>
                                        <h5 class="text-warning fw-bold">المشترك مقدم كتقدمة</h5>
                                        <p class="text-muted mb-0 small">
                                            لا يمكن إدخال دفعات أو طباعة إيصالات للمشتركين المقدمين كتقدمة.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <!-- Show payment form for regular clients -->
                                <div class="border-0 rounded-4 bg-light shadow-sm" style="padding-top: var(--fluid-v-padding); padding-bottom: 0.5rem; padding-left: 1rem; padding-right: 1rem;" wire:key="payment-form-{{ $selectedClient->id }}">
                                    <div class="d-flex align-items-center" style="margin-bottom: var(--fluid-v-gap);">
                                        <div class="bg-white p-2 p-md-3 rounded-circle shadow-sm me-3">
                                            <i class="bi bi-person text-success h4 mb-0"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-dark">{{ $selectedClient->full_name }}</h5>
                                        </div>
                                    </div>

                                    <!-- Current Balance -->
                                    <div class="row" style="margin-bottom: var(--fluid-v-gap);">
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                                                <div class="card-body p-2 text-center">
                                                    <div class="text-muted mb-1">المبلغ المتبقي الحالي</div>
                                                    <div class="h4 fw-bold mb-0 text-primary">
                                                        {{ number_format($selectedClient->current_remaining_usd, 2) }} $
                                                    </div>
                                                    <div class="text-secondary fw-semibold mt-1">
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
                                                           class="form-control @error('discount') is-invalid @enderror"
                                                           step="0.50"
                                                           min="0"
                                                           placeholder="0.00"
                                                           style="text-align: left; box-shadow: shadow-sm;">
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
                                            <div class="col-12" style="margin-top: var(--fluid-v-gap);">
                                                <div class="card border-0 shadow-sm rounded-4 bg-success bg-opacity-10">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex justify-content-center align-items-center gap-3">
                                                            <span class="fw-bold text-success h5 mb-0">إجمالي الدفعة:</span>
                                                            <div class="text-end">
                                                                <div class="h4 fw-bold text-dark mb-0">
                                                                    {{ number_format($this->total_usd, 2) }} $
                                                                </div>
                                                                <div class="text-secondary fw-semibold">
                                                                    ≈ {{ $this->total_lbp }} ل.ل
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Buttons -->
                                            <div class="col-12 text-center" style="margin-top: var(--fluid-v-gap); padding-bottom: 0.5rem;">
                                                <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm py-2 fw-bold" wire:loading.attr="disabled">
                                                    <i class="bi bi-save me-2"></i>
                                                    ادخال الدفعة
                                                    <span wire:loading wire:target="save">
                                                        <i class="bi bi-arrow-repeat spinner fa-spin ms-2"></i>
                                                    </span>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-outline-danger rounded-pill px-4 ms-2 py-2 fw-bold"
                                                        wire:click="resetFilters"
                                                        @click="$refs.searchField.value = ''; $refs.searchField.focus()"
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
                            <div class="text-center py-4 py-md-5">
                                @if($search && $clients->isEmpty())
                                    <div class="py-4 py-md-5">
                                        <i class="bi bi-search display-1 text-muted opacity-25 mb-3 mx-auto"></i>
                                        <h5 class="text-muted fw-bold">لا توجد نتائج للبحث "{{ $search }}"</h5>
                                    </div>
                                @elseif(!$search && $clients->isEmpty())
                                    <div class="py-4 py-md-5">
                                        <i class="bi bi-people display-1 text-muted opacity-25 mb-3 mx-auto"></i>
                                        <h5 class="text-muted fw-bold">لا يوجد مشتركين حتى الآن</h5>
                                    </div>
                                @else
                                    <div class="text-center py-4 py-md-5">
                                        <i class="bi bi-wallet2 display-1 text-success opacity-50 mb-3 mx-auto"></i>
                                        <h5 class="text-muted fw-bold">اختر مشتركاً لبدء إدخال الدفعة</h5>
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
        </div>
    </div>
</div>
