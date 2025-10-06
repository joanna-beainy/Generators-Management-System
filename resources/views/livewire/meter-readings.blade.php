<div class="container mt-4" dir="rtl">
    <!-- Month Header -->
    @php
        $readingMonth = Carbon\Carbon::now()->day <= 3
            ? Carbon\Carbon::now()->subMonth()->startOfMonth()
            : Carbon\Carbon::now()->startOfMonth();
        
        // Traditional Arabic month names
        $arabicMonths = [
            1 => 'كانون الثاني',
            2 => 'شباط',
            3 => 'آذار',
            4 => 'نيسان',
            5 => 'أيار',
            6 => 'حزيران',
            7 => 'تموز',
            8 => 'آب',
            9 => 'أيلول',
            10 => 'تشرين الأول',
            11 => 'تشرين الثاني',
            12 => 'كانون الأول'
        ];
        
        $monthName = $arabicMonths[$readingMonth->month];
        $year = $readingMonth->year;
    @endphp

    <div class="row mb-3">
        <div class="col-md-6">
            <h5 class="mb-1">قراءات العدادات</h5>
            <p class="text-muted mb-0">عن شهر {{ $monthName }} {{ $year }}</p>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-2">
                <input type="text" 
                       wire:model.live.debounce.500ms="search" 
                       class="form-control" 
                       placeholder="ابحث بالاسم أو رقم العميل...">
                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>
                    اغلاق
                </a>
            </div>
        </div>
    </div>

    <!-- Totals Summary -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">إجمالي الاستهلاك</small>
                            <h6 class="mb-0">{{ number_format($readings->sum('consumption')) }} كيلوواط</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">إجمالي المستحقات</small>
                            <h6 class="mb-0">{{ number_format($readings->sum('total_due'), 2) }} د.أ</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(count($readings))
        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-striped table-hover text-center align-middle">
                <thead class="table-secondary" style="position: sticky; top: 0;">
                    <tr>
                        <th>✓</th>
                        <th>الرقم</th>
                        <th>الاسم الكامل</th>
                        <th>الفئة</th>
                        <th>العداد السابق</th>
                        <th>العداد الحالي</th>
                        <th>الاستهلاك</th>
                        <th>سعر الكيلو</th>
                        <th>مبلغ هذا الشهر</th>
                        <th>الصيانة</th>
                        <th>الإجمالي المستحق</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($readings as $reading)
                        <tr wire:key="row-{{ $reading->id }}">
                            {{-- Saved checkmark --}}
                            <td>
                                @if(session("saved_{$reading->id}"))
                                    <span class="text-success fw-bold">✓</span>
                                @endif
                            </td>

                            {{-- Client info --}}
                            <td>{{ $reading->client_id }}</td>
                            <td>{{ $reading->client->fullName() }}</td>
                            <td>{{ $reading->client->MeterCategory->category ?? '-' }}</td>

                            {{-- Meters --}}
                            <td>{{ $reading->previous_meter }}</td>
                            <td>
                                <input type="number"
                                       id="meter-{{ $reading->id }}"
                                       value="{{ $reading->current_meter }}"
                                       wire:blur="updateCurrentMeter({{ $reading->id }}, $event.target.value)"
                                       wire:keydown.enter.prevent="handleEnterKey({{ $reading->id }}, $event.target.value)"
                                       wire:keydown.arrow-down.prevent="handleArrowDown({{ $reading->id }}, $event.target.value)"
                                       wire:keydown.arrow-up.prevent="handleArrowUp({{ $reading->id }}, $event.target.value)"
                                       class="form-control form-control-sm text-center {{ session("error_{$reading->id}") ? 'is-invalid' : '' }}">
                            </td>

                            {{-- Consumption --}}
                            <td>{{ $reading->consumption }} ك.و</td>

                            {{-- Kilowatt price --}}
                            <td>{{ number_format(optional($reading->client->user->kilowattPrice)->price ?? 0, 2) }} د.أ</td>

                            {{-- Amount (for this month) --}}
                            <td>{{ number_format($reading->amount, 2) }} د.أ</td>

                            {{-- Maintenance --}}
                            <td>
                                <input type="number"
                                       step="0.01"
                                       value="{{ $reading->maintenance_cost }}"
                                       wire:blur="updateMaintenanceCost({{ $reading->id }}, $event.target.value)"
                                       class="form-control form-control-sm text-center">
                            </td>

                            {{-- Total Due (amount + maintenance_cost) --}}
                            <td class="fw-bold text-primary">
                                {{ number_format($reading->total_due, 2) }} د.أ
                            </td>
                        </tr>
                        {{-- Error message for this specific row --}}
                        @if(session("error_{$reading->id}"))
                            <tr>
                                <td colspan="11" class="p-2">
                                    <div class="alert alert-danger mb-0 py-2" role="alert">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong class="me-2">خطأ:</strong>
                                                {{ session("error_{$reading->id}") }}
                                            </div>
                                            <button type="button" 
                                                    class="btn-close" 
                                                    onclick="this.parentElement.parentElement.style.display='none'">
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
        <div class="text-center mt-5">
            <h4>لا يوجد قراءات لشهر {{ $monthName }} {{ $year }}</h4>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary mt-3">
                <i class="fas fa-times me-1"></i>
                اغلاق
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Focus navigation between meter input fields
    window.addEventListener('focus-next-meter', event => {
        const ids = Array.from(document.querySelectorAll('[id^="meter-"]')).map(el => el.id.replace('meter-', ''));
        const currentIndex = ids.indexOf(event.detail.currentId.toString());
        const nextId = ids[currentIndex + 1];
        if (nextId) {
            const nextInput = document.getElementById(`meter-${nextId}`);
            nextInput?.focus();
            nextInput?.select();
        }
    });

    window.addEventListener('focus-prev-meter', event => {
        const ids = Array.from(document.querySelectorAll('[id^="meter-"]')).map(el => el.id.replace('meter-', ''));
        const currentIndex = ids.indexOf(event.detail.currentId.toString());
        const prevId = ids[currentIndex - 1];
        if (prevId) {
            const prevInput = document.getElementById(`meter-${prevId}`);
            prevInput?.focus();
            prevInput?.select();
        }
    });

    // Simple alert dismissal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) {
            e.target.closest('.alert').style.display = 'none';
        }
    });
</script>
@endpush