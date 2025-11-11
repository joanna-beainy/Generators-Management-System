<div class="container mt-4" dir="rtl" x-data>
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark no-print">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text-fill text-success me-2"></i>
                        تقرير شهري للمشتركين
                    </h5>
                    <small class="text-muted">عن شهر {{ $arabicMonthName }}</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" 
                            class="btn btn-success btn-sm"
                            onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>
                        طباعة التقرير
                    </button>
                    <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-house me-1"></i>
                        إغلاق
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
             {{-- Alpine.js Auto-Disappearing Alert --}}
            @if ($alertMessage)
                <div 
                    x-data="{ show: true }" 
                    x-show="show" 
                    x-init="setTimeout(() => { show = false; $wire.set('alertMessage', null) }, 5000)" 
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="alert alert-{{ $alertType }} alert-dismissible fade show text-center rounded-3 shadow-sm mb-4 no-print">
                    <i class="bi {{ $alertType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-1"></i>
                    {{ $alertMessage }}
                    <button type="button" class="btn-close" wire:click="$set('alertMessage', null)"></button>
                </div>
            @endif

            <!-- Filters -->
            <div class="row mb-4 no-print">
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

            <!-- Printable Header (Visible only when printing) -->
            <div class="print-header text-center mb-3 d-none d-print-block">
                <h6 class="fw-bold mb-1">تقرير شهري للمشتركين</h6>
                <div>عن شهر {{ $arabicMonthName }}</div>
            </div>

            @if(count($readings))
                <!-- Statistics (Screen Only) -->
                <div class="row mb-4 no-print">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="row text-center fw-bold">
                                    <div class="col">
                                        <div>إجمالي الاستهلاك</div>
                                        <div>{{ $statistics['total_consumption'] }} k.w</div>
                                    </div>
                                    <div class="col">
                                        <div>إجمالي مبلغ هذا الشهر</div>
                                        <div>{{ number_format($statistics['total_amount'], 2) }} $</div>
                                    </div>
                                    <div class="col">
                                        <div>إجمالي تكلفة الصيانة</div>
                                        <div>{{ number_format($statistics['total_maintenance_cost'], 2) }} $</div>
                                    </div>
                                    <div class="col">
                                        <div>إجمالي الرصيد السابق</div>
                                        <div>{{ number_format($statistics['total_previous_balance'], 2) }} $</div>
                                    </div>
                                    <div class="col">
                                        <div>إجمالي المبلغ المستحق</div>
                                        <div>{{ number_format($statistics['total_due'], 2) }} $</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle" id="report-table">
                        <thead class="table-light print-table-header">
                            <tr>
                                <th>الرقم</th>
                                <th>الاسم الكامل</th>
                                <th>العداد السابق</th>
                                <th>العداد الحالي</th>
                                <th>الاستهلاك</th>
                                <th>مبلغ هذا الشهر</th>
                                <th>الصيانة</th>
                                <th>الرصيد السابق</th>
                                <th>الإجمالي المستحق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readings as $reading)
                                @php $isOffered = $reading->client->is_offered; @endphp
                                <tr class="{{ $isOffered ? 'table-info' : '' }}">
                                    <td>{{ $reading->client_id }}</td>
                                    <td>{{ $reading->client->full_name }}</td>
                                    <td>{{ $reading->previous_meter }}</td>
                                    <td>{{ $reading->current_meter }}</td>
                                    <td>{{ $reading->consumption }} k.w</td>
                                    <td>{{ !$isOffered ? number_format($reading->amount, 2).' $' : '-' }}</td>
                                    <td>{{ !$isOffered ? number_format($reading->maintenance_cost, 2).' $' : '-' }}</td>
                                    <td>{{ !$isOffered ? number_format($reading->previous_balance, 2).' $' : '-' }}</td>
                                    <td class="fw-bold {{ !$isOffered && $reading->total_due > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ !$isOffered ? number_format($reading->total_due, 2).' $' : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <!-- Totals Footer (Print Only) -->
                        <tfoot class="print-table-footer d-none d-print-table-row-group">
                            <tr class="totals-row">
                                <td colspan="4">الإجمالي:</td>
                                <td>{{ $statistics['total_consumption'] }} k.w</td>
                                <td>{{ number_format($statistics['total_amount'], 2) }} $</td>
                                <td>{{ number_format($statistics['total_maintenance_cost'], 2) }} $</td>
                                <td>{{ number_format($statistics['total_previous_balance'], 2) }} $</td>
                                <td>{{ number_format($statistics['total_due'], 2) }} $</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-text display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد قراءات</h5>
                    <p class="text-muted">لا توجد قراءات مسجلة للشهر المحدد</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm !important;
            }

            header, footer, nav, .navbar, .sidebar, .no-print {
                display: none !important;
                visibility: hidden !important;
            }

            body, html {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 0;
            }

            .container, .card, .card-body {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                background: white !important;
                box-shadow: none !important;
            }

            /*  Expand table fully when printing (no scroll bar) */
            .table-responsive {
                max-height: none !important;
                overflow: visible !important;
            }

            /*  Excel-like table style */
            table {
                border: 1px solid #000 !important;
                border-collapse: collapse !important;
                width: 100%;
                font-size: 12px !important;
                color: #000 !important;
            }

            th, td {
                border: 1px solid #000 !important;
                padding: 5px !important;
                text-align: center;
                vertical-align: middle;
            }

            thead {
                display: table-header-group !important;
                background-color: #e6e6e6 !important;
                font-weight: bold;
            }
            
            tfoot.print-table-footer {
                display: table-row-group !important; /* render as normal rows so it appears only at the table end */
                background-color: #e6e6e6 !important;
                font-weight: bold;
                /* keep footer together on the last page */
                page-break-inside: avoid !important;
            }

            /* keep rows from being split so footer stays intact */
            tr {
                page-break-inside: avoid !important;
            }

            .print-header {
                display: block !important;
                margin-bottom: 8px;
            }
        }

        @media screen {
            .print-table-footer {
                display: none !important;
            }

            .table-responsive {
                /* ensure the scrolling context for sticky */
                max-height: 45vh;
                overflow: auto;
            }

            .table-responsive thead th {
                position: sticky;
                top: 0;
                background: #e6e6e6; /* match thead bg */
                z-index: 5;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
        }
    </style>
</div>
