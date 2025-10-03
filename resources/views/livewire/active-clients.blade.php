<div class="container" dir="rtl">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 text-center fw-semibold" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i> المشتركين 
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('clients.create') }}" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-plus-circle me-1"></i> إضافة مشترك
            </a>
            <a href="{{ route('trashed.clients.index') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-archive me-1"></i> العملاء المحذوفين
            </a>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    @if($clients->isEmpty())
        <div class="alert alert-light border text-center shadow-sm rounded-3 py-3">
            <i class="bi bi-info-circle me-2"></i> لا يوجد مشتركين حتى الآن.
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
                                <td>{{ $client->fullName() }}</td>
                                <td>{{ $client->phone_number ?? '_' }}</td>
                                <td>{{ $client->address ?? '_' }}</td>
                                <td>{{ $client->generator->name }}</td>
                                <td>{{ $client->meterCategory->category }}</td>
                                <td>
                                    <button wire:click="editClient({{ $client->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="deleteClient({{ $client->id }})"
                                            class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا المشترك؟')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @include('livewire.partials.edit-client')
</div>
