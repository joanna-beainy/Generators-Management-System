<div class="container" dir="rtl">
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

    {{-- Header --}}

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
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-house me-1"></i>
                إغلاق
            </a>
        </div>
    </div>

    {{-- Add Generator Form --}}
    @include('livewire.partials.create-generator')


    {{-- Generator Table --}}
    @if($generators->isEmpty())
        <div class="alert alert-light border text-center shadow-sm rounded-3 py-3">
            <i class="bi bi-info-circle me-2"></i> لا يوجد مولدات حتى الآن.
        </div>
    @else
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover text-center align-middle">
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
                                    {{ $generator->clientsCount() > 0 ? $generator->clientsCount() : '_' }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill shadow-sm"
                                            x-data="{ generatorId: {{ $generator->id }} }"
                                            @click="if(confirm('هل أنت متأكد من حذف هذا المولد؟')) { $wire.call('deleteGenerator', generatorId) }"
                                            title="حذف">
                                        <i class="bi bi-trash"></i>
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
