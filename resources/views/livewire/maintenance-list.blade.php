<div class="container mt-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-tools me-2"></i>
                        مصاريف الصيانة للمشترك
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
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($client)
                @if($maintenances->count() > 0)
                    <!-- Summary -->
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <small class="text-muted">إجمالي مصاريف الصيانة</small>
                                            <h6 class="mb-0"> {{ number_format($maintenances->sum('amount'), 2) }} د.أ</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                            {{ number_format($maintenance->amount, 2) }} د.أ
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