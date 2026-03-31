<div class="container d-flex flex-column" style="height: 100%; overflow: hidden;" dir="rtl">

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

    <div class="flex-shrink-0 d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-clock-history text-success me-2"></i> سجل الدفعات للمشترك
            </h3>

            @if($client)
                <div class="mt-2 text-end">
                    <div class="d-inline-flex align-items-center bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-4 py-2">
                        <i class="bi bi-person-badge fs-4 me-2"></i>
                        <span class="fw-bold fs-5">{{ $client->full_name }}</span>
                        <span class="mx-3 opacity-50">|</span>
                        <span class="fs-6 fw-bold">الرقم: {{ $client->id }}</span>
                    </div>
                </div>
            @endif
        </div>
        <div class="d-flex gap-2 text-end">
            @if($client)
                <a href="{{ route('client.meter.readings', ['clientId' => $client->id ?? '']) }}" class="btn btn-success rounded-pill shadow-sm px-4">
                    <i class="bi bi-speedometer2 me-1"></i>
                    قراءات العدادات
                </a>
            @endif
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3" style="min-height: 0;">
        <div class="card-body p-4 d-flex flex-column flex-grow-1" style="min-height: 0;">

            @if($client)
                <div class="flex-shrink-0 row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold"><i class="bi bi-calendar3 me-1"></i> السنة</label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select wire:model.live="selectedYear" class="form-select border-0" style="text-align: right; box-shadow: none;">
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold"><i class="bi bi-calendar-month me-1"></i> الشهر</label>
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
                    <div class="table-responsive flex-grow-1 rounded-3 border" style="overflow-y: auto;">
                        <table class="table table-hover text-center align-middle mb-0">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                                <tr class="text-uppercase small fw-bold">
                                    <th>التاريخ</th>
                                    <th>عن شهر</th>
                                    <th>المبلغ المدفوع $</th>
                                    <th>الخصم $</th>
                                    <th>الرصيد بعد الدفعة $</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($payments as $payment)
                                    @php
                                        $isDeleted = $payment->trashed();
                                        $canDelete = !$isDeleted && $payment->meter_reading_id === $this->latestCompletedReadingId;
                                        $deletedTextClass = $isDeleted ? 'text-danger' : '';
                                    @endphp
                                    <tr class="{{ $isDeleted ? 'table-danger bg-danger bg-opacity-10' : '' }}">
                                        <td>
                                            <div class="fw-bold {{ $deletedTextClass }}">{{ $payment->paid_at->format('d-m-Y') }}</div>
                                            <small class="{{ $isDeleted ? 'text-danger' : 'text-secondary' }}">{{ $payment->paid_at->format('H:i') }}</small>
                                            @if($isDeleted)
                                                <div class="small text-danger mt-1">
                                                    <span>تاريخ الحذف: {{ $payment->deleted_at?->format('d-m-Y H:i') }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold {{ $deletedTextClass }}">
                                                {{ $payment->meterReading->reading_for_month->format('m-Y') }}
                                            </span>
                                        </td>
                                        <td class="fw-bold {{ $isDeleted ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td>
                                            @if($payment->discount > 0)
                                                <span class="fw-bold {{ $isDeleted ? 'text-danger' : 'text-info' }}">
                                                    {{ number_format($payment->discount, 2) }}
                                                </span>
                                            @else
                                                <span class="{{ $isDeleted ? 'text-danger small' : 'text-muted small' }}">---</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold {{ $isDeleted ? 'text-danger' : ($payment->remaining_after_payment <= 0 ? 'text-success' : 'text-danger') }}">
                                            {{ number_format($payment->remaining_after_payment, 2) }}
                                        </td>
                                        <td>
                                            @if($canDelete)
                                                <button
                                                    wire:click="confirmDelete({{ $payment->id }})"
                                                    class="btn btn-outline-danger btn-sm rounded-pill shadow-sm"
                                                    title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @elseif($isDeleted)
                                                <span class="text-danger small">تم الحذف</span>
                                            @else
                                                <span class="text-muted small">---</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 flex-grow-1 d-flex flex-column justify-content-center">
                        <i class="bi bi-clock-history display-1 text-success opacity-25 mb-3 mx-auto"></i>
                        <h5 class="text-muted fw-bold">لا توجد دفعات مسجلة</h5>
                        <p class="text-secondary small mb-0">لم يتم إدخال أي دفعات لهذا المشترك في الفترة المحددة</p>
                    </div>
                @endif
            @else
                <div class="text-center py-5 flex-grow-1 d-flex flex-column justify-content-center">
                    <i class="bi bi-person-x display-1 text-danger opacity-25 mb-3 mx-auto"></i>
                    <h5 class="text-danger fw-bold">المشترك غير موجود</h5>
                    <p class="text-secondary">يرجى اختيار مشترك صحيح</p>
                </div>
            @endif
        </div>
    </div>
</div>
