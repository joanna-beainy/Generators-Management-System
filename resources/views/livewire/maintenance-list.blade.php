<div class="container d-flex flex-column" style="height: 100%; overflow: hidden;" dir="rtl">
    
    {{-- Alpine.js Auto-Disappearing Alert --}}


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
                <i class="bi bi-tools text-success me-2"></i> مصاريف الصيانة للمشترك
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
        <div class="text-end">
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>
    
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3" style="min-height: 0;">
        <div class="card-body p-4 d-flex flex-column" style="min-height: 0;">
            @if($client)
                @if($maintenances->count() > 0)
                    <!-- Maintenance Records Table -->
                    <div class="table-responsive flex-grow-1 rounded-3 border" style="overflow-y: auto;">
                        <table class="table table-hover text-center align-middle mb-0">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                                <tr class="text-uppercase small fw-bold">
                                    <th>الوصف</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenances as $maintenance)
                                    <tr>
                                        <td>
                                            @if($maintenance->description)
                                                {{ $maintenance->description }}
                                            @else
                                                <span class="text-muted small">---</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($maintenance->amount, 2) }} $</td>
                                        <td class="text-secondary">{{ $maintenance->created_at->format('d-m-Y') }}</td>
                                        <td>
                                            <button 
                                                wire:click="confirmDelete({{ $maintenance->id }})"
                                                class="btn btn-outline-danger btn-sm rounded-pill shadow-sm"
                                                title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 flex-grow-1 d-flex flex-column justify-content-center">
                        <i class="bi bi-clipboard-check display-1 text-success opacity-50 mb-3 mx-auto"></i>
                        <h5 class="text-muted fw-bold">لا توجد مصاريف صيانة مسجلة</h5>
                        <p class="text-secondary small mb-0">لم يتم إدخال أي مصاريف صيانة لهذا المشترك حتى الآن.</p>
                    </div>
                @endif
            @else
                <div class="text-center py-5 flex-grow-1 d-flex flex-column justify-content-center">
                    <i class="bi bi-person-x display-1 text-danger opacity-25 mb-3 mx-auto"></i>
                    <h5 class="text-danger fw-bold">المشترك غير موجود</h5>
                    <p class="text-secondary">يرجى اختيار مشترك صحيح لعرض سجل الصيانة</p>
                </div>
            @endif
        </div>
    </div>
</div>