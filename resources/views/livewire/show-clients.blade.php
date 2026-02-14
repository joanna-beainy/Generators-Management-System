<div class="container" dir="rtl" x-data="{ editModalOpen: @entangle('showEditModal'), editingClientId: null, modalLoading: false }">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-people-fill text-success me-2"></i> المشتركين 
            </h3>
            <p class="text-secondary mb-0 mt-1">إدارة بيانات المشتركين والتحكم في حالاتهم</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clients.create') }}" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-person-plus-fill me-1"></i> إضافة مشترك
            </a>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <!-- Alert -->
    @if ($alertMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false, $wire.set('alertMessage', null) }, 5000)" 
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="alert alert-{{ $alertType }} border-0 text-center rounded-3 shadow-sm mb-4 ">
            <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
            {{ $alertMessage }}
            <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
        </div>
    @endif

    <!-- Search and Filter Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4 bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-search me-1"></i> ابحث عن المشترك</label>
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                        <input type="text" 
                               wire:model="search" 
                               wire:keydown.enter="handleSearch"
                               class="form-control border-0 px-3" 
                               placeholder="اكتب اسم المشترك أو رقمه..."
                               autofocus
                               style="text-align: right; box-shadow: none;">
                        <button class="btn btn-white border-0" type="button" wire:click="handleSearch">
                            <i class="bi bi-search text-secondary"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <label class="form-label fw-bold text-secondary"><i class="bi bi-person-check me-1"></i> اختيار مشترك</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedClientId" class="form-select border-0 px-3" style="text-align: right; box-shadow: none;">
                            <option value="">-- اختر المشترك من القائمة --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                                    {{ $client->id }} - {{ $client->full_name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @if($selectedClientId)
                    <div class="col-md-2">
                        <button wire:click="resetFilters" class="btn btn-outline-success w-100 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-arrow-clockwise me-1"></i> عرض الكل
                        </button>
                    </div>
                @endif
            </div>
            
            <div wire:loading wire:target="handleSearch" class="small text-muted mt-2 px-2">
                <i class="bi bi-arrow-repeat spinner me-1 text-success"></i> جاري البحث...
            </div>
        </div>
    </div>

    @if($displayClients->isEmpty())
        <div class="alert alert-light border-0 text-center shadow-sm rounded-4 py-5 bg-white">
            <i class="bi bi-people display-4 text-success opacity-25 mb-3 d-block"></i>
            <h5 class="text-dark fw-bold">لا يوجد مشتركين مطابقين</h5>
            @if($search || $selectedClientId)
                <p class="text-muted mb-4">لا توجد نتائج للبحث الحالي</p>
                <button wire:click="resetFilters" class="btn btn-success rounded-pill px-4 shadow-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع المشتركين
                </button>
            @else
                <p class="text-muted mb-4">لم يتم إضافة أي مشتركين حتى الآن</p>
                <a href="{{ route('clients.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> إضافة أول مشترك
                </a>
            @endif
        </div>
    @else
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 58vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr class="text-uppercase small fw-bold">
                                <th>الرقم</th>
                                <th>الاسم الكامل</th>
                                <th>الهاتف</th>
                                <th>العنوان</th>
                                <th>المولد</th>
                                <th>الفئة</th>
                                <th>النوع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($displayClients as $client)
                                <tr wire:key="client-row-{{ $client->id }}">
                                    <td class="fw-bold text-dark">{{ $client->id }}</td>
                                    <td class="fw-bold text-dark">{{ $client->full_name }}</td>
                                    <td>{{ $client->phone_number ?? '-' }}</td>
                                    <td>{{ $client->address }}</td>
                                    <td>{{ $client->generator->name }}</td>
                                    <td>{{ $client->meterCategory ? $client->meterCategory->category : '-' }}</td>
                                    <td>
                                        @if($client->is_offered)
                                            تقدمة
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-3">
                                            <div class="form-check form-switch pt-1">
                                                <input class="form-check-input" type="checkbox" role="switch" 
                                                       wire:click="toggleActive({{ $client->id }})"
                                                       {{ $client->is_active ? 'checked' : '' }}
                                                       style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                            </div>
                                            <button @click="editingClientId = {{ $client->id }}; editModalOpen = true; modalLoading = true; $wire.call('editClient', {{ $client->id }}).then(() => { modalLoading = false })"
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