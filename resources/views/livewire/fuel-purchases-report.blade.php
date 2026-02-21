<div class="container d-flex flex-column" style="height: 100%; overflow: hidden;" dir="rtl">

    <!-- Header Section -->
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-fuel-pump text-success me-2"></i> إدارة شراء الوقود
            </h3>
            @if($selectedMonth && $selectedYear)
                <p class="text-secondary mb-0 mt-1">
                    <i class="bi bi-calendar3 me-1"></i> لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                </p>
            @endif
        </div>
        <div class="d-flex gap-2 text-end">
            <a href="{{ route('fuel.consumption.report') }}" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-droplet me-1"></i> استهلاك الوقود
            </a>
            <button wire:click="openPurchaseModal" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-plus-circle me-1"></i> إضافة شراء
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
    <div class="flex-shrink-0 row g-3 mb-4 no-print">
        <!-- Persistent Metric: Available Fuel -->
        <div class="col-md-4">
            <div class="card border-success shadow-sm rounded-4">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-droplet fs-4 text-success me-2"></i>
                            <h6 class="text-success mb-0 fw-bold">اللترات المتاحة حالياً</h6>
                        </div>
                        <h5 class="fw-bold text-success mb-0">{{ number_format($totalAvailableLiters) }} لتر</h5>
                    </div>
                </div>
            </div>
        </div>
        @if($purchases->count() > 0)
            <!-- Period Metric: Total Purchased -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted medium mb-1">إجمالي اللترات المشتراة (للفترة)</div>
                        <div class="h5 fw-bold mb-0 text-dark">
                            {{ number_format($purchases->sum('liters_purchased')) }} <span class="small font-monospace">لتر</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Period Metric: Total Price -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted medium mb-1">المبلغ المنفق (للفترة)</div>
                        <div class="h5 fw-bold mb-0 text-primary">
                            {{ number_format($purchases->sum('total_price'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3" style="min-height: 0;">
        <div class="card-body p-4 d-flex flex-column" style="min-height: 0;">
            <!-- Filters -->
            <div class="flex-shrink-0 row g-3 mb-4 no-print">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-calendar-event me-1"></i> السنة</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedYear" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-calendar-month me-1"></i> الشهر</label>
                    <div class="shadow-sm rounded-pill overflow-hidden border">
                        <select wire:model.live="selectedMonth" class="form-select border-0" style="text-align: right; box-shadow: none;">
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if($purchases->count() > 0)
                <!-- Purchases Table -->
                <div class="table-responsive flex-grow-1 rounded-3 border" style="overflow-y: auto;">
                    <table class="table table-hover text-center align-middle mb-0">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                            <tr class="text-uppercase small fw-bold">
                                <th></th>
                                <th>الشركة</th>
                                <th>اللترات المشتراة</th>
                                <th>السعر الإجمالي</th>
                                <th>التاريخ</th>
                                <th>الوصف</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $purchase->company }}</td>
                                    <td>{{ number_format($purchase->liters_purchased) }} لتر</td>
                                    <td>{{ number_format($purchase->total_price, 2) }} $</td>
                                    <td class="text-muted">{{ $purchase->created_at->format('d-m-Y') }}</td>
                                    <td>{{ $purchase->description }}</td>
                                    <td>
                                        <button 
                                            wire:click="confirmDelete({{ $purchase->id }})"
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
                    <i class="bi bi-fuel-pump display-4 mb-3 text-success mx-auto"></i>
                    <h5 class="text-muted fw-bold">لا توجد مشتريات وقود</h5>
                    <p class="text-muted mb-0">لم يتم العثور على أي عمليات شراء في هذه الفترة.</p>
                    @if($totalAvailableLiters > 0)
                        <div class="mt-3 text-success small">
                            <i class="bi bi-info-circle me-1"></i>
                            يوجد وقود متاح من مشتريات سابقة: {{ number_format($totalAvailableLiters) }} لتر
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Include Add Purchase Modal Partial -->
    @if($showPurchaseModal)
        @include('livewire.partials.add-fuel-purchase-modal')
    @endif
</div>