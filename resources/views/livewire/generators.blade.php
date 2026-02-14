<div class="container mt-2" dir="rtl">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="bi bi-lightning-fill text-success me-2"></i> المولدات الخاصة بك
        </h3>
        <div class="d-flex gap-2">
            <button type="button" 
                class="btn btn-success rounded-pill shadow-sm px-4"
                    wire:click="toggleAddForm">
                <i class="bi bi-plus-circle me-1"></i>إضافة مولد جديد
            </button>
            <a href="{{ route('generators.maintenance.report') }}" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-tools me-1"></i>
                صيانة المولدات
            </a>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-house me-1"></i>
                إغلاق
            </a>
        </div>
    </div>

    <!-- Alpine.js Auto-Disappearing Alert -->
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

    <!-- Add Generator Form -->
    @include('livewire.partials.create-generator')


    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            @if($generators->isEmpty())
                <div class="text-center py-5 bg-white">
                    <i class="bi bi-lightning display-4 text-success opacity-25 mb-3 d-block"></i>
                    <h5 class="text-dark fw-bold">لا يوجد مولدات حاليًا</h5>
                    <p class="text-muted">قم بإضافة أول مولد للبدء في إدارة المشتركين</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle mb-0">
                        <thead class="table-secondary">
                            <tr class="text-uppercase small fw-bold">
                                <th>اسم المولد</th>
                                <th>عدد المشتركين</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($generators as $generator)
                                <tr wire:key="generator-row-{{ $generator->id }}">
                                    <td class="fw-bold text-dark">{{ $generator->name }}</td>
                                    <td>
                                        {{ $generator->clientsCount() }} مشترك
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none"
                                                wire:click="confirmDelete({{ $generator->id }})"
                                                title="حذف">
                                            <i class="bi bi-trash3 h5 mb-0"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
