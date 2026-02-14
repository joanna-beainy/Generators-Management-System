<div class="container mt-2" dir="rtl" 
    x-data="{
        focusMeterId: @entangle('focusMeterId'),
        
        init() {
            // Auto-focus when focusMeterId changes
            this.$watch('focusMeterId', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        const el = document.getElementById('meter-' + value);
                        if (el) {
                            el.focus();
                            el.select();
                        }
                    });
                }
            });

        }
    }">
    
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-speedometer2 text-success me-2"></i> قراءات العدادات
            </h3>
            @if($arabicMonthName)
                <p class="text-secondary mb-0 mt-1"><i class="bi bi-calendar3 me-1"></i> شهر {{ $arabicMonthName }}</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <!-- Alpine.js Auto-Disappearing Alert (Global) -->
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

    @if($displayReadings->count())
        <!-- Statistics Section -->
        <div class="row g-3 mb-4 no-print">
            <div class="col-md">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">إجمالي الاستهلاك</div>
                        <div class="h5 fw-bold mb-0 text-dark">
                            {{ $displayReadings->filter(fn($r) => !$r->client->is_offered)->sum('consumption') }} <span class="small font-monospace">k.w</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">إجمالي التقدمة</div>
                        <div class="h5 fw-bold mb-0 text-info">
                            {{ $displayReadings->filter(fn($r) => $r->client->is_offered)->sum('consumption') }} <span class="small font-monospace">k.w</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">إجمالي مبلغ الشهر</div>
                        <div class="h5 fw-bold mb-0 text-success">
                            {{ number_format($displayReadings->filter(fn($r) => !$r->client->is_offered)->sum('amount'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">إجمالي الصيانة</div>
                        <div class="h5 fw-bold mb-0 text-primary">
                            {{ number_format($displayReadings->filter(fn($r) => !$r->client->is_offered)->sum('maintenance_cost'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">إجمالي الرصيد السابق</div>
                        <div class="h5 fw-bold mb-0 text-primary">
                            {{ number_format($displayReadings->filter(fn($r) => !$r->client->is_offered)->sum('previous_balance'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">إجمالي المبلغ المستحق</div>
                        <div class="h5 fw-bold mb-0 text-danger">
                            {{ number_format($displayReadings->filter(fn($r) => !$r->client->is_offered)->sum('total_due'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
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
                            wire:keydown.enter="handleSearch"
                            class="form-control border-0" 
                            placeholder="اكتب اسم المشترك أو رقمه..."
                            wire:loading.attr="disabled"
                            style="text-align: right; box-shadow: none;">
                        <button class="btn btn-white border-0" type="button" wire:click="handleSearch">
                            <i class="bi bi-search text-secondary"></i>
                        </button>
                    </div>
                    <div wire:loading wire:target="handleSearch" class="small text-muted mt-1 px-2">
                        <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-person-check me-1"></i>اختيار مشترك</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedClientId" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            <option value="">-- اختر المشترك --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                                    {{ $client->id }} - {{ $client->full_name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if($search || $selectedClientId)
                <div class="row mb-4">
                    <div class="col-12 text-end">
                        <button wire:click="resetFilters" class="btn btn-outline-success rounded-pill btn-sm px-3">
                            <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع القراءات
                        </button>
                    </div>
                </div>
            @endif

            @if($displayReadings->count())
                <div class="table-responsive rounded-3 border" style="max-height: 43vh; overflow-y: auto;">
                    <table class="table table-hover text-center align-middle mb-0;">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr class="text-uppercase small fw-bold">
                                <th>✓</th>
                                <th style="width: 10px;">الرقم</th>
                                <th>الاسم الكامل</th>
                                <th>السابق</th>
                                <th style="width: 150px;">الحالي</th>
                                <th>الاستهلاك k.w</th>
                                <th>سعر الكيلو $</th>
                                <th>سعر الاشتراك $</th>
                                <th>المبلغ $</th>
                                <th>الصيانة $</th>
                                <th>الرصيد السابق $</th>
                                <th>الإجمالي المستحق $</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displayReadings as $reading)
                                @php
                                    $isOffered = $reading->client->is_offered;
                                    $hasReading = !is_null($reading->reading_date);
                                    $hasFieldError = isset($fieldErrors[$reading->id]);
                                @endphp
                                <tr wire:key="row-{{ $reading->id }}" class="{{ $isOffered ? 'table-info' : '' }}">
                                    <td>
                                        @if($savedReadings[$reading->id] ?? false)
                                            <span class="text-success fw-bold">✓</span>
                                        @endif
                                    </td>
                                    <td>{{ $reading->client_id }}</td>
                                    <td>{{ $reading->client->full_name }}</td>
                                    <td>{{ $reading->previous_meter }}</td>
                                    <td x-data="{ localError: '' }">
                                        <input
                                            type="number"
                                            id="meter-{{ $reading->id }}"
                                            value="{{ $hasReading ? $reading->current_meter : '' }}"
                                            class="form-control form-control-sm text-center {{ $hasFieldError ? 'is-invalid' : '' }}"
                                            x-bind:class="{ 'is-invalid': localError !== '' }"
                                            x-effect="if ($el === document.activeElement) { $el.select(); }"
                                            x-on:input="localError = ''"

                                            x-on:keydown="
                                                const prev = {{ (int) $reading->previous_meter }};
                                                const curr = {{ (int) $reading->current_meter }};
                                                const hasReading = {{ $hasReading ? 'true' : 'false' }};

                                                const handle = (moveNext) => {
                                                    const val = parseInt($event.target.value, 10);
                                                    if (isNaN(val)) return;

                                                    if (val < prev) {
                                                        localError = 'العداد الحالي يجب أن يكون أكبر من العداد السابق.';
                                                        return;
                                                    }

                                                    // FIRST TIME
                                                    if (!hasReading) {
                                                        $wire.call('updateCurrentMeter', {{ $reading->id }}, val, moveNext ? 'next' : null);
                                                        return;
                                                    }

                                                    // SECOND TIME – same value → do nothing
                                                    if (val === curr) return;

                                                    // SECOND TIME – different value → confirm
                                                    window.dispatchEvent(new CustomEvent('show-confirm-modal', {
                                                        detail: {
                                                            readingId: {{ $reading->id }},
                                                            value: val,
                                                            oldMeter: curr
                                                        }
                                                    }));
                                                };

                                                if ($event.key === 'Enter' || $event.key === 'ArrowDown') {
                                                    $event.preventDefault();
                                                    handle(true);
                                                }

                                                if ($event.key === 'ArrowUp') {
                                                    $event.preventDefault();
                                                    const row = $event.target.closest('tr');
                                                    let prevRow = row?.previousElementSibling;
                                                    while (prevRow) {
                                                        const input = prevRow.querySelector('input[type=number]');
                                                        if (input) {
                                                            input.focus();
                                                            input.select();
                                                            break;
                                                        }
                                                        prevRow = prevRow.previousElementSibling;
                                                    }
                                                }
                                            "

                                            x-on:blur="
                                                if ({{ $hasReading ? 'true' : 'false' }}) return;

                                                const val = parseInt($event.target.value, 10);
                                                const prev = {{ (int) $reading->previous_meter }};
                                                if (isNaN(val)) return;

                                                if (val < prev) {
                                                    localError = 'العداد الحالي يجب أن يكون أكبر من العداد السابق.';
                                                    return;
                                                }

                                                $wire.call('updateCurrentMeter', {{ $reading->id }}, val);
                                            "
                                        >

                                        <!-- CLIENT-SIDE ERROR (IMMEDIATE) -->
                                        <div
                                            x-show="localError !== ''"
                                            class="invalid-feedback d-block text-start small"
                                            x-text="localError">
                                        </div>

                                        <!-- SERVER-SIDE ERROR -->
                                        @if($hasFieldError)
                                            <div class="invalid-feedback d-block text-start small">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                {{ $fieldErrors[$reading->id] }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ $reading->consumption }}</td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format(optional($reading->client->user->kilowattPrice)->price ?? 0, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format(optional($reading->client->meterCategory)->price?? 0, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format($reading->amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format($reading->maintenance_cost, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format($reading->previous_balance, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="fw-bold text-primary">
                                        @if(!$isOffered)
                                            {{ number_format($reading->total_due, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border text-center shadow-sm rounded-3 py-5">
                    <i class="bi bi-speedometer2 display-4 mb-3 text-success"></i>
                    <h5 class="text-muted">لا يوجد قراءات</h5>
                    @if($search || $selectedClientId)
                        <p class="text-muted mb-3">لا توجد نتائج للبحث</p>
                        <button wire:click="resetFilters" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع القراءات
                        </button>
                    @else
                        <p class="text-muted mb-3">لا توجد قراءات متاحة حالياً</p>
                    @endif
                </div>
            @endif


                @include('livewire.partials.confirm-meter-update-modal')

        </div>
    </div>
</div>

