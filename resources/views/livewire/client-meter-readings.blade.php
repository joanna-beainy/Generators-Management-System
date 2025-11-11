<div class="container mt-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2 text-success me-2"></i>
                        قراءات العدادات للمشترك
                    </h5>
                    @if($client)
                        <small class="opacity-75">{{ $client->full_name }} - الرقم: {{ $client->id }}</small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if($client)
                        <a href="{{ route('payment.history', ['clientId' => $client->id]) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-clock-history me-1"></i>
                            سجل الدفعات
                        </a>
                    @endif
                    <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-house me-1"></i>
                        إغلاق
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
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
                            <option value="">كل الأشهر</option>
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($readings->count() > 0)
                    <!-- Meter Readings Table -->
                    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-hover text-center align-middle">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>الشهر</th>
                                    <th>العداد السابق</th>
                                    <th>العداد الحالي</th>
                                    <th>الاستهلاك</th>
                                    <th>مبلغ هذا الشهر</th>
                                    <th>الصيانة</th>
                                    <th>الرصيد السابق</th>
                                    <th>المبلغ المتبقي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($readings as $reading)
                                    <tr>
                                        <td>
                                            {{ $reading->reading_for_month->format('m-Y') }}
                                        </td>
                                        <td>{{ $reading->previous_meter }}</td>
                                        <td>{{ $reading->current_meter }}</td>
                                        <td>{{ $reading->current_meter - $reading->previous_meter }}</td>
                                        <td class="fw-bold">{{ number_format($reading->amount, 2) }} $</td>
                                        <td class="fw-bold">{{ number_format($reading->maintenance_cost, 2) }} $</td>
                                        <td class="fw-bold">{{ number_format($reading->previous_balance, 2) }} $</td>
                                        <td class="fw-bold {{ $reading->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($reading->remaining_amount, 2) }} $
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-speedometer2 display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد قراءات مسجلة</h5>
                        <p class="text-muted">لم يتم إدخال أي قراءات لهذا المشترك في الفترة المحددة</p>
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
