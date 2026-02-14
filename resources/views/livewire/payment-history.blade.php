<div class="container mt-2" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-clock-history text-success me-2"></i> سجل الدفعات للمشترك
            </h3>
            
            @if($client)
                <div class="mt-2">
                    <div class="d-inline-flex align-items-center bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-4 py-2">
                        <i class="bi bi-person-badge fs-4 me-2"></i>
                        <span class="fw-bold fs-5">{{ $client->full_name }}</span>
                        <span class="mx-3 opacity-50">|</span>
                        <span class="fs-6 fw-bold">الرقم: {{ $client->id }}</span>
                    </div>
                </div>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($client)
                <a href="{{ route('client.meter.readings', ['clientId' => $client->id ?? '']) }}" class="btn btn-success rounded-pill shadow-sm px-4">
                    <i class="bi bi-speedometer2 me-1"></i>
                    قراءات العدادات
                </a>
            @endif
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

            @if($client)
                <!-- Filters -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary"><i class="bi bi-calendar3 me-1"></i> السنة</label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select wire:model.live="selectedYear" class="form-select border-0" style="text-align: right; box-shadow: none;">
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary"><i class="bi bi-calendar-month me-1"></i> الشهر</label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select wire:model.live="selectedMonth" class="form-select border-0" style="text-align: right; box-shadow: none;">
                                @foreach($months as $key => $month)
                                    <option value="{{ $key }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if($payments->count() > 0)
                    <!-- Payments Table -->
                    <div class="table-responsive rounded-3 border" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-hover text-center align-middle mb-0">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                                <tr class="text-uppercase small fw-bold">
                                    <th>التاريخ</th>
                                    <th>عن شهر</th>
                                    <th>المبلغ المدفوع</th>
                                    <th>الخصم</th>
                                    <th>الرصيد بعد الدفعة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $payment->paid_at->format('d-m-Y') }}</div>
                                            <small class="text-secondary">{{ $payment->paid_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold">
                                                {{ $payment->meterReading->reading_for_month->format('m-Y') }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-success">
                                            {{ number_format($payment->amount, 2) }} $
                                        </td>
                                        <td>
                                                @if($payment->discount > 0)
                                                    <span class="fw-bold text-info">
                                                        {{ number_format($payment->discount, 2) }} $
                                                    </span>
                                                @else
                                                    <span class="text-muted small">---</span>
                                                @endif
                                        </td>
                                        <td class="fw-bold {{ $payment->remaining_after_payment <= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($payment->remaining_after_payment, 2) }} $
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history display-1 text-success opacity-50 mb-3"></i>
                        <h5 class="text-muted fw-bold">لا توجد دفعات مسجلة</h5>
                        <p class="text-secondary small mb-0">لم يتم إدخال أي دفعات لهذا المشترك في الفترة المحددة</p>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-person-x display-1 text-danger opacity-25 mb-3"></i>
                    <h5 class="text-danger fw-bold">المشترك غير موجود</h5>
                    <p class="text-secondary">يرجى اختيار مشترك صحيح</p>
                </div>
            @endif
        </div>
    </div>
</div>