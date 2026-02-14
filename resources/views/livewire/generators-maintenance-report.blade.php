<div>
    <div class="container mt-2" dir="rtl">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">
                    <i class="bi bi-tools text-success me-2"></i> إدارة مصاريف الصيانة
                </h3>
                @if($selectedMonth && $selectedYear)
                    <p class="text-secondary mb-0 mt-1">
                        <i class="bi bi-calendar3 me-1"></i> لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                        @if($selectedGenerator !== 'all')
                            - {{ $generators->where('id', $selectedGenerator)->first()->name ?? '' }}
                        @endif
                    </p>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('manage.generators') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                    <i class="bi bi-arrow-right me-1"></i> العودة
                </a>
                <button wire:click="openMaintenanceModal" class="btn btn-success rounded-pill shadow-sm px-4">
                    <i class="bi bi-plus-circle me-1"></i> إضافة صيانة
                </button>
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
                x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="alert alert-{{ $alertType }} border-0 text-center rounded-3 shadow-sm mb-4">
                <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                {{ $alertMessage }}
                <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
            </div>
        @endif

        @if($maintenances->count() > 0)
            <div class="row g-3 mb-4 no-print">
                <!-- Total Maintenance -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                        <div class="card-body p-3 text-center">
                            <div class="text-muted small mb-1">إجمالي مصاريف الصيانة</div>
                            <div class="h5 fw-bold mb-0 text-dark">
                                {{ number_format($maintenances->sum('amount'), 2) }} <span class="small font-monospace">$</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-white">
                <!-- Filters -->
                <div class="row g-3 mb-4 align-items-end border-bottom pb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary"><i class="bi bi-calendar-event me-1"></i>السنة</label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select wire:model.live="selectedYear" class="form-select border-0 px-3" style="box-shadow: none;">
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary"><i class="bi bi-calendar-month me-1"></i>الشهر</label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select wire:model.live="selectedMonth" class="form-select border-0 px-3" style="box-shadow: none;">
                                @foreach($months as $key => $month)
                                    <option value="{{ $key }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary"><i class="bi bi-lightning-charge me-1"></i>المولد</label>
                        <div class="shadow-sm rounded-pill overflow-hidden border">
                            <select wire:model.live="selectedGenerator" class="form-select border-0 px-3" style="box-shadow: none;">
                                <option value="all">جميع المولدات</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                @if($maintenances->count() > 0)
                    <!-- Maintenances Table -->
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover text-center align-middle mb-0">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                                <tr class="text-uppercase small fw-bold">
                                    <th></th>
                                    <th>المولد</th>
                                    <th>المبلغ ($)</th>
                                    <th>التاريخ</th>
                                    <th>الوصف</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($maintenances as $maintenance)
                                    <tr wire:key="maintenance-{{ $maintenance->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-dark">
                                            {{ $maintenance->generator->name }}
                                        </td>
                                        <td class="fw-bold text-success">{{ number_format($maintenance->amount, 2) }} $</td>
                                        <td class="text-secondary small">{{ $maintenance->created_at->format('d-m-Y') }}</td>
                                        <td class="text-secondary">{{ $maintenance->description }}</td>
                                        <td>
                                            <button 
                                                wire:click="confirmDelete({{ $maintenance->id }})"
                                                class="btn btn-link text-danger p-0 text-decoration-none"
                                                title="حذف">
                                                <i class="bi bi-trash3 h5 mb-0"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 bg-white rounded-4 border-0">
                        <i class="bi bi-tools display-4 text-success opacity-25 mb-3 d-block"></i>
                        <h5 class="text-dark fw-bold">لا توجد مصاريف صيانة في هذه الفترة</h5>
                        @if($selectedGenerator !== 'all')
                            <p class="text-muted">للمولد المحدد: {{ $generators->where('id', $selectedGenerator)->first()->name ?? '' }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Include Add Maintenance Modal Partial -->
        @if($showMaintenanceModal)
            @include('livewire.partials.add-generator-maintenance-modal')
        @endif
    </div>
</div>