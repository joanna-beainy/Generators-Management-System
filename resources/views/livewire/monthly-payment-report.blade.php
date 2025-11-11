<div>
    <div class="container mt-4" dir="rtl">
        <div class="card shadow-sm">
            <div class="card-header bg-light text-dark no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-cash-coin text-success me-2"></i>
                            تقرير تحصيل شهري
                        </h5>
                        @if($selectedMonth && $selectedYear)
                            <small class="text-muted">
                                 لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                            </small>
                        @endif
                    </div>
                    <div>
                        <button onclick="window.print()" class="btn btn-success btn-sm">
                            <i class="bi bi-printer me-1"></i> طباعة
                        </button>
                        <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-house me-1"></i> إغلاق
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
                    <h6 class="fw-bold mb-1">تقرير تحصيل شهري</h6>
                    <div>لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}</div>
                </div>

                @if($payments->count() > 0)
                    <!-- Statistics (Screen Only) -->
                    <div class="row mb-4 no-print">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row text-center fw-bold">
                                        <div class="col">
                                            <div>إجمالي المبلغ المقبوض</div>
                                            <div>{{ number_format($payments->sum('amount'), 2) }} $</div>
                                        </div>
                                        <div class="col">
                                            <div>إجمالي الخصومات</div>
                                            <div>{{ number_format($payments->sum('discount'), 2) }} $</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <div class="table-responsive">
                        <table class="table text-center align-middle">
                            <thead class="table-secondary print-table-header" >
                                <tr>
                                    <th>الرقم</th>
                                    <th>المشترك</th>
                                    <th>تاريخ الدفع</th>
                                    <th>عن شهر</th>
                                    <th>المبلغ المقبوض</th>
                                    <th>الخصم</th>
                                    <th>الرصيد بعد الدفعة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $payment->client->full_name }}</td>
                                        <td>{{ $payment->paid_at->format('d-m-Y') }}</td>
                                        <td>{{ $payment->meterReading->reading_for_month->format('m-Y') }}</td>
                                        <td>{{ number_format($payment->amount, 2) }} $</td>
                                        <td>{{ number_format($payment->discount, 2) }} $</td>
                                        <td>{{ number_format($payment->remaining_after_payment, 2) }} $</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <!-- Totals Footer (Print Only) -->
                            <tfoot class="print-table-footer d-none d-print-table-row-group">
                                <tr class="totals-row">
                                    <td colspan="4">الإجمالي:</td>
                                    {{-- sum amount for this month  --}}
                                    <td>{{ number_format($payments->sum('amount'), 2) }} $</td>
                                    <td>{{ number_format($payments->sum('discount'), 2) }} $</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-cash-coin display-4 text-muted mb-3"></i>
                        <h5>لا توجد دفعات في هذه الفترة</h5>
                    </div>
                @endif
            </div>
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

            /* Expand table fully when printing (no scroll bar) */
            .table-responsive {
                max-height: none !important;
                overflow: visible !important;
            }

            /* Excel-like table style */
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
