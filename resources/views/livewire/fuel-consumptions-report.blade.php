<div class="container d-flex flex-column" style="height: 100%; overflow: hidden;" dir="rtl">
    <!-- Header Section -->
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-droplet-half text-success me-2"></i> إدارة استهلاك الوقود
            </h3>
            @if($selectedMonth && $selectedYear)
                <p class="text-secondary mb-0 mt-1">
                    <i class="bi bi-calendar3 me-1"></i> لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                    @if($selectedGenerator !== 'all')
                        <span class="badg bg-light text-dark border ms-2">{{ $generators->where('id', $selectedGenerator)->first()->name ?? '' }}</span>
                    @endif
                </p>
            @endif
        </div>
        <div class="d-flex gap-2 text-end">
            <a href="{{ route('fuel.purchase.report') }}" class="btn btn-outline-secondary fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-arrow-right me-1"></i> إدارة الشراء 
            </a>
            <button wire:click="openConsumptionModal" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-plus-circle me-1"></i> إضافة استهلاك
            </button>
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

    <!-- Statistics Section -->
    @if($consumptions->count() > 0)
    <div class="flex-shrink-0 row g-3 mb-4 no-print">
        <!-- Total Consumption -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-3 text-center">
                    <div class="text-muted medium mb-1">إجمالي اللترات المستهلكة</div>
                    <div class="h5 fw-bold mb-0 text-dark">
                        {{ number_format($consumptions->sum('liters_consumed')) }} <span class="small font-monospace">لتر</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3" style="min-height: 0;">
        <div class="card-body p-4 d-flex flex-column" style="min-height: 0;">
            <!-- Filters -->
            <div class="flex-shrink-0 row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="bi bi-calendar-event me-1"></i> السنة</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedYear" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="bi bi-calendar-month me-1"></i> الشهر</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedMonth" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="bi bi-lightning-charge me-1"></i> المولد</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedGenerator" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            <option value="all">جميع المولدات</option>
                            @foreach($generators as $generator)
                                <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            @if($consumptions->count() > 0)
                <!-- Consumptions Table -->
                <div class="table-responsive flex-grow-1 rounded-3 border" style="overflow-y: auto;">
                    <table class="table table-hover text-center align-middle mb-0">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                            <tr class="text-uppercase small fw-bold">
                                <th class="py-3"></th>
                                <th class="py-3">المولد</th>
                                <th class="py-3">اللترات المستهلكة</th>
                                <th class="py-3">التاريخ</th>
                                <th class="py-3">ملاحظات</th>
                                <th class="py-3">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consumptions as $consumption)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">
                                        {{ $consumption->generator->name }}
                                    </td>
                                    <td>{{ number_format($consumption->liters_consumed) }} لتر</td>
                                    <td class="text-muted">{{ $consumption->created_at->format('d-m-Y') }}</td>
                                    <td class="small">{{ $consumption->notes ?? '-' }}</td>
                                    <td>
                                        <button 
                                            wire:click="confirmDelete({{ $consumption->id }})"
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
                <div class="alert alert-light border text-center shadow-sm rounded-3 py-5 flex-grow-1 d-flex flex-column justify-content-center">
                    <i class="bi bi-droplet-half display-4 mb-3 text-success mx-auto"></i>
                    <h5 class="text-muted fw-bold">لا توجد سجلات استهلاك</h5>
                    <p class="text-muted mb-0">لم يتم العثور على أي عمليات استهلاك وقود في هذه الفترة.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Include Add Consumption Modal Partial -->
    @if($showConsumptionModal)
        @include('livewire.partials.add-fuel-consumption-modal')
    @endif
</div>