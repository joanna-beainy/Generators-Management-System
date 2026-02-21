<div class="container d-flex flex-column" style="height: 100%; overflow: hidden;" dir="rtl" x-data="{ editModalOpen: @entangle('showEditModal'), editingClientId: null, modalLoading: false }">
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center mb-4">
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
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

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


    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden flex-shrink-0">
        <div class="card-body p-4 bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-md-10">
                    <label class="form-label fw-bold"><i class="bi bi-search me-1"></i> ابحث عن المشترك</label>
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               wire:keydown.enter="handleSearch"
                               class="form-control border-0 px-3" 
                               placeholder="اكتب اسم المشترك أو رقمه..."
                               autofocus
                               style="text-align: right; box-shadow: none;">
                        <button class="btn btn-white border-0" type="button" wire:click="handleSearch">
                            <i class="bi bi-search text-secondary"></i>
                        </button>
                    </div>
                    @if($showSearchResults && $search)
                    <div class="list-group w-100 shadow-sm border rounded-3 mt-1 overflow-auto bg-white" style="max-height: 260px;">
                        @forelse($clients as $client)
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    wire:click="selectClient({{ $client->id }})">
                                <span>{{ $client->id }} - {{ $client->full_name }}</span>
                                <i class="bi bi-person text-muted"></i>
                            </button>
                        @empty
                            <div class="list-group-item text-muted small">
                                لا توجد نتائج
                            </div>
                        @endforelse
                    </div>
                    @endif
                </div>
                
                @if($selectedClientId)
                    <div class="col-md-2">
                        <button wire:click="resetFilters" class="btn btn-outline-success w-100 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-arrow-clockwise me-1"></i> عرض الكل
                        </button>
                    </div>
                @endif
            </div>
            
            <div wire:loading wire:target="handleSearch,search" class="small text-muted mt-2 px-2">
                <i class="bi bi-arrow-repeat spinner me-1 text-success"></i> جاري البحث...
            </div>
        </div>
    </div>

    @if($displayClients->isEmpty())
        <div class="alert alert-light border-0 text-center shadow-sm rounded-4 py-5 bg-white flex-grow-1 d-flex flex-column justify-content-center">
            <i class="bi bi-people display-4 text-success opacity-25 mb-3 d-block"></i>
            <h5 class="text-dark fw-bold">لا يوجد مشتركين مطابقين</h5>
            @if($search || $selectedClientId)
                <p class="text-muted mb-4">لا توجد نتائج للبحث الحالي</p>
                <button wire:click="resetFilters" class="btn btn-success rounded-pill px-4 shadow-sm mx-auto">
                    <i class="bi bi-arrow-clockwise me-1"></i> عرض جميع المشتركين
                </button>
            @else
                <p class="text-muted mb-4">لم يتم إضافة أي مشتركين حتى الآن</p>
                <a href="{{ route('clients.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm mx-auto">
                    <i class="bi bi-person-plus-fill me-1"></i> إضافة أول مشترك
                </a>
            @endif
        </div>
    @else
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3" style="min-height: 0;">
            <div class="card-body p-0 d-flex flex-column" style="min-height: 0;">
                <div class="table-responsive flex-grow-1" style="overflow-y: auto;">
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
                                    <td>{{ $client->is_offered ? 'تقدمة' : '-' }}</td>
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

    @include('livewire.partials.edit-client')
</div>
