@section('title', 'سجل الدفعات للمشترك')

<div class="container mt-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        سجل الدفعات للمشترك
                    </h5>
                    @if($client)
                        <small class="opacity-75">{{ $client->full_name }} - الرقم: {{ $client->id }}</small>
                    @endif
                </div>
                <a href="{{ route('users.dashboard') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-house me-1"></i>
                    إغلاق
                </a>
            </div>
        </div>
        
        <div class="card-body">
            @if($client)
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">السنة</label>
                        <select wire:model.live="selectedYear" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">الشهر</label>
                        <select wire:model.live="selectedMonth" class="form-select">
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                @if($payments->count() > 0)
                    <!-- Payments Table -->
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-striped table-hover text-center align-middle">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
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
                                            <div>{{ $payment->paid_at->format('d-m-Y') }}</div>
                                            <small class="text-muted">{{ $payment->paid_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            {{ $payment->meterReading->reading_for_month->format('m-Y') }}
                                        </td>
                                        <td class="fw-bold">
                                            {{ number_format($payment->amount, 2) }} $
                                        </td>
                                        <td class="fw-bold">
                                            {{ number_format($payment->discount, 2) }} $
                                        </td>
                                        <td class="fw-bold ">
                                            {{ number_format($payment->remaining_after_payment, 2) }} $
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد دفعات مسجلة</h5>
                        <p class="text-muted">لم يتم إدخال أي دفعات لهذا المشترك في الفترة المحددة</p>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">المشترك غير موجود</h5>
                    <p class="text-muted">يرجى اختيار مشترك صحيح</p>
                </div>
            @endif
        </div>
    </div>
</div>