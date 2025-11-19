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

    <!-- Header -->
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2 text-success me-2"></i> قراءات العدادات
                    </h5>
                    @if($arabicMonthName)
                        <p class="text-muted mb-0">عن شهر {{ $arabicMonthName }}</p>
                    @endif
                </div>
                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house me-1"></i>
                    إغلاق
                </a>
            </div>
        </div>

        <div class="card-body">
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
                </div>
            </div>

            @if($search || $selectedClientId)
                <div class="row mb-3">
                    <div class="col-12">
                        <button wire:click="resetFilters" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع القراءات
                        </button>
                    </div>
                </div>
            @endif

            <!-- Statistics (Screen Only) -->
            <div class="row mb-4 no-print">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <div class="row text-center fw-bold">
                                <div class="col border-end">
                                    <div>إجمالي الاستهلاك</div>
                                    <div>{{ $displayReadings->sum('consumption') }} k.w</div>
                                </div>
                                <div class="col border-end">
                                    <div>إجمالي مبلغ هذا الشهر</div>
                                    <div>{{ number_format(
                                            $displayReadings
                                                ->filter(fn($r) => !$r->client->is_offered)
                                                ->sum('amount'),
                                            2
                                        ) }} $
                                    </div>
                                </div>
                                <div class="col border-end">
                                    <div>إجمالي الرصيد السابق</div>
                                    <div>{{ number_format(
                                            $displayReadings
                                                ->filter(fn($r) => !$r->client->is_offered)
                                                ->sum('previous_balance'),
                                            2
                                        ) }} $
                                    </div>
                                </div>
                                <div class="col">
                                    <div>إجمالي المبلغ المستحق</div>
                                    <div>{{ number_format(
                                            $displayReadings
                                                ->filter(fn($r) => !$r->client->is_offered)
                                                ->sum('total_due'),
                                            2
                                        ) }} $
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Readings Table -->
            @if($displayReadings->count())
                <div class="table-responsive" style="max-height: 48vh; overflow-y: auto;">
                    <table class="table table-hover text-center align-middle">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>✓</th>
                                <th>الرقم</th>
                                <th>الاسم الكامل</th>
                                <th>العداد السابق</th>
                                <th>العداد الحالي</th>
                                <th>الاستهلاك</th>
                                <th>سعر الكيلو</th>
                                <th>سعر الاشتراك</th>
                                <th>مبلغ هذا الشهر</th>
                                <th>الصيانة</th>
                                <th>الرصيد السابق</th>
                                <th>الإجمالي المستحق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displayReadings as $reading)
                                @php
                                    $isOffered = $reading->client->is_offered;
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
                                    <td>
                                        <input type="number"
                                            id="meter-{{ $reading->id }}"
                                            wire:blur="updateCurrentMeter({{ $reading->id }}, $event.target.value)"
                                            wire:keydown.enter.prevent="handleEnterKey({{ $reading->id }}, $event.target.value)"
                                            wire:keydown.arrow-down.prevent="handleArrowDown({{ $reading->id }}, $event.target.value)"
                                            wire:keydown.arrow-up.prevent="handleArrowUp({{ $reading->id }}, $event.target.value)"
                                            class="form-control form-control-sm text-center {{ $hasFieldError ? 'is-invalid' : '' }}"
                                            {{ $isOffered ? 'style="background-color: #e3f2fd;"' : '' }}
                                            x-effect="if ($el === document.activeElement) { $el.select(); }">
                                        @if($hasFieldError)
                                            <div class="invalid-feedback d-block text-start small">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                {{ $fieldErrors[$reading->id] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $reading->consumption }} k.w</td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format(optional($reading->client->user->kilowattPrice)->price ?? 0, 2) }} $
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format(optional($reading->client->meterCategory)->price?? 0, 2) }} $
                                        @else
                                            -
                                        @endif
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format($reading->amount, 2) }} $
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format($reading->maintenance_cost, 2) }} $
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$isOffered)
                                            {{ number_format($reading->previous_balance, 2) }} $
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="fw-bold text-primary">
                                        @if(!$isOffered)
                                            {{ number_format($reading->total_due, 2) }} $
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
                    <i class="bi bi-speedometer2 display-4 text-muted mb-3"></i>
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
        </div>
    </div>
</div>