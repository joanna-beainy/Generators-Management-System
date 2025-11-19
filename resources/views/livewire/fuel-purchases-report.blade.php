<div>
    <div class="container mt-2" dir="rtl">
        <div class="card shadow-sm">
            <div class="card-header bg-light text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-fuel-pump text-success me-2"></i>
                            إدارة شراء الوقود
                        </h5>
                        @if($selectedMonth && $selectedYear)
                            <small class="text-muted">
                                 لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                            </small>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('fuel.consumption.report') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-droplet"></i> استهلاك الوقود
                        </a>
                        <button wire:click="openPurchaseModal" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> إضافة شراء
                        </button>
                        <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-house me-1"></i> إغلاق
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Alert --}}
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

                <!-- Available Fuel Card -->
                <div class="row mb-4">
                    <div class="col-md-5">
                        <div class="card border-success">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-droplet fs-4 text-success me-2"></i>
                                        <h6 class="text-success mb-0">اللترات المتاحة</h6>
                                    </div>
                                    <h5 class="fw-bold text-success mb-0">{{ number_format($totalAvailableLiters) }} لتر</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">السنة</label>
                        <select wire:model.live="selectedYear" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">الشهر</label>
                        <select wire:model.live="selectedMonth" class="form-select">
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Period Statistics -->
                <div class="row mb-4 no-print">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="row text-center fw-bold">
                                    <div class="col border-end">
                                        <div>إجمالي اللترات المشتراة</div>
                                        <div>{{ number_format($purchases->sum('liters_purchased')) }} لتر</div>
                                    </div>
                                    <div class="col">
                                        <div>المبلغ المنفق</div>
                                        <div>{{ number_format($purchases->sum('total_price'), 2) }} $</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($purchases->count() > 0)
                    <!-- Purchases Table -->
                    <div class="table-responsive" style="max-height: 38vh; overflow-y: auto;">
                        <table class="table text-center align-middle">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>الرقم</th>
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
                                        <td>{{ $purchase->company }}</td>
                                        <td>{{ number_format($purchase->liters_purchased) }} لتر</td>
                                        <td>{{ number_format($purchase->total_price, 2) }} $</td>
                                        <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                                        <td>{{ $purchase->description }}</td>
                                        <td>
                                            <button 
                                                wire:click="deletePurchase({{ $purchase->id }})"
                                                wire:confirm="هل أنت متأكد من حذف شراء الوقود هذا؟"
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
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-fuel-pump display-4 text-muted mb-3"></i>
                        <h5>لا توجد مشتريات وقود في هذه الفترة</h5>
                        @if($totalAvailableLiters > 0)
                            <p class="text-success mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                يوجد وقود متاح من مشتريات سابقة: {{ number_format($totalAvailableLiters) }} لتر
                            </p>
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
</div>