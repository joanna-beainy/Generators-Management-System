<div class="container" dir="rtl">
    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 text-center fw-semibold" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 text-center fw-semibold" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="bi bi-archive text-secondary me-2"></i> العملاء المحذوفين
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('active.clients.index') }}" class="btn btn-outline-primary rounded-pill shadow-sm px-4">
                <i class="bi bi-arrow-left"></i> المشتركين 
            </a>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    {{-- Clients Table --}}
    @if($clients->isEmpty())
        <div class="alert alert-light border text-center shadow-sm rounded-3 py-3">
            <i class="bi bi-info-circle me-2"></i> لا يوجد عملاء محذوفين
        </div>
    @else
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-secondary">
                        <tr>
                            <th>الاسم الكامل</th>
                            <th>الهاتف</th>
                            <th>العنوان</th>
                            <th>المولد</th>
                            <th>الفئة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td>{{$client->fullName()}}</td>
                                <td>{{ $client->phone_number ?? '_' }}</td>
                                <td>{{ $client->address ?? '_' }}</td>
                                <td>{{ $client->generator->name }}</td>
                                <td>{{ $client->meterCategory->category }}</td>
                                <td>
                                    <button wire:click="restoreClient({{ $client->id }})"
                                            class="btn btn-sm btn-outline-success rounded-pill px-3"
                                            onclick="return confirm('هل أنت متأكد من استعادة هذا المشترك؟')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
