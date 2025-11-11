<div class="container mt-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-currency-dollar text-success me-2"></i>
                        تقرير عن المبالغ المستحقة
                    </h5>
                    <small class="opacity-75">عرض جميع المشتركين الذين لديهم مبالغ غير مدفوعة بالكامل</small>
                </div>
                <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house me-1"></i>
                    إغلاق
                </a>
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

            
            @if($unpaidClients->count() > 0)
                {{-- Statistics --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="row text-center fw-bold">
                                    <div class="col">
                                        <div>إجمالي المبالغ المستحقة</div>
                                        <div class="text-danger">
                                            {{ number_format($unpaidClients->sum(function($client) {
                                                return $client->meterReadings->first()->remaining_amount;
                                            }), 2) }} $
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-striped table-hover text-center align-middle">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>رقم المشترك</th>
                                <th>الاسم الكامل</th>
                                <th>المبلغ المستحق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unpaidClients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td>{{ $client->full_name }}</td>
                                    <td class="fw-bold text-danger">{{ number_format($client->meterReadings->first()->remaining_amount, 2) }} $</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                    <h5 class="text-muted">لا يوجد مشتركين لديهم مبالغ مستحقة</h5>
                    <p class="text-muted">جميع المشتركين قاموا بتسديد مستحقاتهم بالكامل</p>
                </div>
            @endif
        </div>
    </div>
</div>