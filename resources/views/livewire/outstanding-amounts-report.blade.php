<div class="container mt-2" dir="rtl">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-cash-stack text-success me-2"></i> تقرير المبالغ المستحقة
            </h3>
            <p class="text-secondary mb-0 mt-1">
                <i class="bi bi-info-circle me-1"></i> عرض جميع المشتركين الذين لديهم مبالغ غير مدفوعة بالكامل
            </p>
        </div>
        <div>
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

    <!-- Statistics Section -->
    @if($unpaidClients->count() > 0)
    <div class="row mb-4 no-print">
        <div class="col-md-5">
            <div class="card border-danger shadow-sm rounded-4">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-circle fs-4 text-danger me-2"></i>
                            <h6 class="text-danger mb-0 fw-bold">إجمالي المبالغ المستحقة</h6>
                        </div>
                        <h5 class="fw-bold text-danger mb-0">
                            {{ number_format($unpaidClients->sum(function($client) {
                                return $client->meterReadings->first()->remaining_amount;
                            }), 2) }} $
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4">
            
            @if($unpaidClients->count() > 0)
                <div class="table-responsive rounded-3 border" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-hover text-center align-middle mb-0">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                            <tr class="text-uppercase small fw-bold">
                                <th>رقم المشترك</th>
                                <th>الاسم الكامل</th>
                                <th>المبلغ المستحق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unpaidClients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td class="fw-bold text-dark">{{ $client->full_name }}</td>
                                    <td class="fw-bold text-danger">{{ number_format($client->meterReadings->first()->remaining_amount, 2) }} $</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border text-center shadow-sm rounded-3 py-5">
                    <i class="bi bi-check-circle-fill display-4 mb-3 text-success"></i>
                    <h5 class="text-success fw-bold">لا توجد مبالغ مستحقة!</h5>
                    <p class="text-muted mb-0">جميع المشتركين قاموا بتسديد مستحقاتهم بالكامل.</p>
                </div>
            @endif
        </div>
    </div>
</div>