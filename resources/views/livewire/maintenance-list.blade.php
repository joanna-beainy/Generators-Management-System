<div class="container mt-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-tools text-success me-2"></i>
                        مصاريف الصيانة للمشترك
                    </h5>
                    @if($client)
                        <small class="opacity-75">{{ $client->full_name }} - الرقم: {{ $client->id }}</small>
                    @endif
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

            @if($client)
                @if($maintenances->count() > 0)
                    <!-- Maintenance Records Table -->
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-striped table-hover text-center align-middle">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>الوصف</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenances as $maintenance)
                                    <tr>
                                        <td>{{ $maintenance->description ?? '---' }}</td>
                                        <td class="fw-bold text-success">
                                            {{ number_format($maintenance->amount, 2) }} $
                                        </td>
                                        <td>{{ $maintenance->created_at->format('d-m-Y') }}</td>
                                        <td>
                                            <button 
                                                wire:click="deleteMaintenance({{ $maintenance->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذه الصيانة ؟"
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
                    <div class="text-center py-5">
                        <i class="bi bi-tools display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد مصاريف صيانة مسجلة</h5>
                        <p class="text-muted">لم يتم إدخال أي مصاريف صيانة لهذا المشترك</p>
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