<div>
    <div class="container mt-2" dir="rtl">
        <div class="card shadow-sm">
            <div class="card-header bg-light text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-tools text-success me-2"></i>
                            إدارة مصاريف صيانة المولدات
                        </h5>
                        @if($selectedMonth && $selectedYear)
                            <small class="text-muted">
                                 لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                                 @if($selectedGenerator !== 'all')
                                    - {{ $generators->where('id', $selectedGenerator)->first()->name ?? '' }}
                                 @endif
                            </small>
                        @endif
                    </div>
                    <div>
                        <button wire:click="openMaintenanceModal" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> إضافة صيانة
                        </button>
                        <a href="{{ route('manage.generators') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-return-left"></i> العودة 
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

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">السنة</label>
                        <select wire:model.live="selectedYear" class="form-select">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">الشهر</label>
                        <select wire:model.live="selectedMonth" class="form-select">
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">المولد</label>
                        <select wire:model.live="selectedGenerator" class="form-select">
                            <option value="all">جميع المولدات</option>
                            @foreach($generators as $generator)
                                <option value="{{ $generator->id }}">{{ $generator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                @if($maintenances->count() > 0)
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row text-center fw-bold">
                                        <div class="col">
                                            <div>إجمالي مصاريف الصيانة</div>
                                            <div>{{ number_format($maintenances->sum('amount'), 2) }} $</div>
                                        </div>
                                        @if($selectedGenerator !== 'all')
                                        <div class="col border-start">
                                            <div>المولد المحدد</div>
                                            <div>{{ $generators->where('id', $selectedGenerator)->first()->name ?? '' }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenances Table -->
                    <div class="table-responsive" style="max-height: 48vh; overflow-y: auto;">
                        <table class="table text-center align-middle">
                            <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>الرقم</th>
                                    <th>المولد</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>الوصف</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenances as $maintenance)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $maintenance->generator->name }}
                                        </td>
                                        <td>{{ number_format($maintenance->amount, 2) }} $</td>
                                        <td>{{ $maintenance->created_at->format('d-m-Y') }}</td>
                                        <td>{{ $maintenance->description }}</td>
                                        <td>
                                            <button 
                                                wire:click="deleteMaintenance({{ $maintenance->id }})"
                                                wire:confirm="هل أنت متأكد من حذف مصاريف الصيانة هذه؟"
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
                        <i class="bi bi-tools display-4 text-success mb-3"></i>
                        <h5>لا توجد مصاريف صيانة في هذه الفترة</h5>
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