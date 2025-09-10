<div class="container" dir="rtl">
    {{-- Flash Messages --}}
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
            <i class="bi bi-lightning-charge-fill text-warning me-2"></i> المولدات الخاصة بك
        </h3>
        <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-success shadow-sm px-4 rounded-pill"
                    wire:click="toggleAddForm">
                <i class="bi bi-plus-circle me-1"></i>
                {{ $showAddForm ? 'إخفاء النموذج' : 'إضافة مولد جديد' }}
            </button>
            <a href="{{ route('users.dashboard') }}"
               class="btn btn-outline-secondary shadow-sm px-4 rounded-pill">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    {{-- Add Generator Form --}}
    @if($showAddForm)
        <form wire:submit.prevent="addGenerator" class="card card-body mb-4 shadow-sm rounded-4 border-0 bg-light">
            <div class="row g-3 align-items-center">
                <div class="col-md-9">
                    <input type="text"
                           wire:model.defer="name"
                           class="form-control @error('name') is-invalid @enderror rounded-pill"
                           placeholder="اسم المولد">
                    @error('name')
                        <div class="invalid-feedback text-end">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-success w-100 shadow-sm rounded-pill">
                        <i class="bi bi-check2-circle me-1"></i> إضافة
                    </button>
                </div>
            </div>
        </form>
    @endif

    {{-- Generator Table --}}
    @if($generators->isEmpty())
        <div class="alert alert-light border text-center shadow-sm rounded-3 py-3">
            <i class="bi bi-info-circle me-2"></i> لا يوجد مولدات حتى الآن.
        </div>
    @else
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-secondary">
                        <tr>
                            <th>اسم المولد</th>
                            <th>عدد المشتركين</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($generators as $generator)
                            <tr>
                                <td class="fw-semibold">{{ $generator->name }}</td>
                                <td>
                                    {{ $generator->customers_count > 0 ? $generator->customers_count : '_' }}
                                </td>
                                <td>
                                    <button wire:click="deleteGenerator({{ $generator->id }})"
                                            class="btn btn-outline-danger btn-sm rounded-pill shadow-sm"
                                            title="حذف"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا المولد؟')">
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
</div>
