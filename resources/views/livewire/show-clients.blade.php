<div class="container" dir="rtl">
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

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="bi bi-people-fill text-success me-2"></i> المشتركين 
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('clients.create') }}" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-plus-circle me-1"></i> إضافة مشترك
            </a>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <!-- Search and Client Selection -->
    <div class="row mb-4">
        <div class="col-md-6">
            <label class="form-label fw-bold">ابحث عن المشترك</label>
            <div class="input-group">
                <input type="text" 
                       wire:model="search" 
                       wire:keydown.enter="handleSearch"
                       class="form-control" 
                       placeholder="اكتب اسم المشترك أو رقمه..."
                       wire:loading.attr="disabled"
                       style="text-align: right;">
                <button class="btn btn-outline-secondary" type="button" wire:click="handleSearch">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div wire:loading wire:target="handleSearch" class="small text-muted mt-1">
                <i class="bi bi-arrow-repeat spinner me-1"></i> جاري البحث...
            </div>
        </div>
        
        <div class="col-md-6">
            <label class="form-label fw-bold">المشترك المحدد</label>
            <select wire:model.live="selectedClientId" class="form-select" style="text-align: right;">
                <option value="">-- اختر المشترك --</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                        {{ $client->id }} - {{ $client->full_name }} 
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if($search || $selectedClientId)
        <div class="row mb-3">
            <div class="col-12">
                <button wire:click="resetFilters" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع المشتركين
                </button>
            </div>
        </div>
    @endif

    @if($displayClients->isEmpty())
        <div class="alert alert-light border text-center shadow-sm rounded-3 py-5">
            <i class="bi bi-people display-4 text-muted mb-3"></i>
            <h5 class="text-muted">لا يوجد مشتركين</h5>
            @if($search || $selectedClientId)
                <p class="text-muted mb-3">لا توجد نتائج للبحث </p>
                <button wire:click="resetFilters" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع المشتركين
                </button>
            @else
                <p class="text-muted mb-3">لم يتم إضافة أي مشتركين حتى الآن</p>
                <a href="{{ route('clients.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> إضافة أول مشترك
                </a>
            @endif
        </div>
    @else
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 text-center ">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <td>الرقم</td>
                                <th>الاسم الكامل</th>
                                <th>الهاتف</th>
                                <th>العنوان</th>
                                <th>المولد</th>
                                <th>الفئة</th>
                                <th>النوع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displayClients as $client)
                                <tr>
                                    <td class="fw-bold">{{ $client->id }}</td>
                                    <td>{{ $client->full_name }}</td>
                                    <td>{{ $client->phone_number ?? '-' }}</td>
                                    <td>{{ $client->address }}</td>
                                    <td>{{ $client->generator->name }}</td>
                                    <td>{{ $client->meterCategory ? $client->meterCategory->category : '-' }}</td>
                                    <td>{{$client->is_offered ? 'تقدمة' : '-'}}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" 
                                                       wire:click="toggleActive({{ $client->id }})"
                                                       {{ $client->is_active ? 'checked' : '' }}>
                                            </div>
                                            <button wire:click="editClient({{ $client->id }})" 
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    title="تعديل البيانات">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Include Edit Modal Partial -->
    @include('livewire.partials.edit-client')
</div>