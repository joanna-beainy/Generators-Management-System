<div class="container mt-4" dir="rtl" 
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
         },
         
         // Helper to dismiss session alerts
         dismissAlert(readingId = null) {
             if (readingId) {
                 // You can add Livewire call to clear specific session alert if needed
                 const alertRow = document.querySelector(`[data-alert="${readingId}"]`);
                 if (alertRow) {
                     alertRow.style.display = 'none';
                 }
             }
         }
     }">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-speedometer2 text-primary me-2"></i> قراءات العدادات
            </h3>
            @if($arabicMonthName)
                <p class="text-muted mb-0">عن شهر {{ $arabicMonthName }}</p>
            @endif
        </div>
        <div>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

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
                    <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع المشتركين
                </button>
            </div>
        </div>
    @endif

    <!-- Summary -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">إجمالي الاستهلاك</small>
                            <h6 class="mb-0">{{ number_format($readings->sum('consumption')) }} ك.و</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">إجمالي المستحقات</small>
                            <h6 class="mb-0">
                                {{ number_format(
                                    $readings
                                        ->filter(fn($r) => !$r->client->is_offered)
                                        ->sum('total_due'),
                                    2
                                ) }} د.أ
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Readings Table -->
    @if(count($readings))
        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-striped table-hover text-center align-middle">
                <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th>✓</th>
                        <th>الرقم</th>
                        <th>الاسم الكامل</th>
                        <th>العداد السابق</th>
                        <th>العداد الحالي</th>
                        <th>الاستهلاك</th>
                        <th>سعر الكيلو</th>
                        <th>مبلغ هذا الشهر</th>
                        <th>الصيانة</th>
                        <th>الرصيد السابق</th>
                        <th>الإجمالي المستحق</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($readings as $reading)
                        @php
                            $isOffered = $reading->client->is_offered;
                            $hasError = session("error_{$reading->id}");
                            $hasWarning = session("warning_{$reading->id}");
                            $hasSuccess = session("success_{$reading->id}");
                            $alertType = $hasError ? 'error' : ($hasWarning ? 'warning' : ($hasSuccess ? 'success' : null));
                            $alertMessage = $hasError ? session("error_{$reading->id}") : 
                                           ($hasWarning ? session("warning_{$reading->id}") : 
                                           ($hasSuccess ? session("success_{$reading->id}") : null));
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
                                       value="{{ $reading->current_meter }}"
                                       wire:blur="updateCurrentMeter({{ $reading->id }}, $event.target.value)"
                                       wire:keydown.enter.prevent="handleEnterKey({{ $reading->id }}, $event.target.value)"
                                       wire:keydown.arrow-down.prevent="handleArrowDown({{ $reading->id }}, $event.target.value)"
                                       wire:keydown.arrow-up.prevent="handleArrowUp({{ $reading->id }}, $event.target.value)"
                                       class="form-control form-control-sm text-center {{ $hasError ? 'is-invalid' : '' }}"
                                       :class="{ 'is-invalid': {{ $hasError ? 'true' : 'false' }} }"
                                       {{ $isOffered ? 'style="background-color: #e3f2fd;"' : '' }}
                                       x-effect="if ($el === document.activeElement) { $el.select(); }">
                            </td>
                            <td>{{ $reading->consumption }} ك.و</td>
                            <td>
                                @if(!$isOffered)
                                    {{ number_format(optional($reading->client->user->kilowattPrice)->price ?? 0, 2) }} د.أ
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!$isOffered)
                                    {{ number_format($reading->amount, 2) }} د.أ
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!$isOffered)
                                    {{ number_format($reading->maintenance_cost, 2) }} د.أ
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!$isOffered)
                                    {{ number_format($reading->previous_balance, 2) }} د.أ
                                @else
                                    -
                                @endif
                            </td>
                            <td class="fw-bold text-primary">
                                @if(!$isOffered)
                                    {{ number_format($reading->remaining_amount, 2) }} د.أ
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                        @if($alertType)
                            <tr data-alert="{{ $reading->id }}"
                                x-data="{
                                    show: true,
                                    type: '{{ $alertType }}',
                                    message: `{{ $alertMessage }}`,
                                    getAlertClass() {
                                        const classes = {
                                            'success': 'alert-success',
                                            'error': 'alert-danger', 
                                            'warning': 'alert-warning',
                                        };
                                        return classes[this.type] || 'alert-info';
                                    },
                                    getAlertIcon() {
                                        const icons = {
                                            'success': 'fa-check-circle',
                                            'error': 'fa-exclamation-triangle',
                                            'warning': 'fa-exclamation-triangle',
                                        };
                                        return icons[this.type] || 'fa-info-circle';
                                    }
                                }"
                                x-show="show"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95">
                                <td colspan="12" class="p-2">
                                    <div class="alert mb-0 py-2" 
                                         :class="getAlertClass()" 
                                         role="alert">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="fas me-2" :class="getAlertIcon()"></i>
                                                <span x-html="message"></span>
                                            </div>
                                            <button type="button" 
                                                    class="btn-close" 
                                                    @click="show = false">
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
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