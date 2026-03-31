<div class="container d-flex flex-column" style="height: 100%; overflow: hidden;" dir="rtl" id="report-root">
    <!-- Header Section -->
    <div class="flex-shrink-0 d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-cash-coin text-success me-2"></i> تقرير تحصيل شهري
            </h3>
            @if($selectedMonth && $selectedYear)
                <p class="text-secondary mb-0 mt-1">
                    <i class="bi bi-calendar3 me-1"></i> لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}
                </p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-success rounded-pill shadow-sm px-4">
                <i class="bi bi-printer me-1"></i> طباعة
            </button>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>
    

    @if($payments->count() > 0)
        <!-- Statistics Section -->
        <div class="flex-shrink-0 row g-3 mb-4 no-print">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted medium mb-1">إجمالي المبلغ المقبوض</div>
                        <div class="h5 fw-bold mb-0 text-success">
                            {{ number_format($payments->sum('amount'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted medium mb-1">إجمالي الخصومات</div>
                        <div class="h5 fw-bold mb-0 text-primary">
                            {{ number_format($payments->sum('discount'), 2) }} $
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

            <!-- Printable Header (Visible only when printing) -->
            <div class="print-header text-center mb-3 d-none d-print-block">
                <h6 class="fw-bold mb-1">تقرير تحصيل شهري</h6>
                <div>لشهر {{ $months[$selectedMonth] ?? '' }} {{ $selectedYear }}</div>
            </div>

            @if($payments->count() > 0)
                <!-- Payments Table -->
                <div class="table-responsive flex-grow-1 rounded-3 border" style="overflow-y: auto;">
                    <table class="table table-hover text-center align-middle mb-0">
                        <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
                            <tr class="text-uppercase small fw-bold">
                                <th>الرقم</th>
                                <th>المشترك</th>
                                <th>تاريخ الدفع</th>
                                <th>عن شهر</th>
                                <th>المبلغ المقبوض $</th>
                                <th>الخصم $</th>
                                <th>الرصيد بعد الدفعة $</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->client->id }}</td>
                                    <td>{{ $payment->client->full_name }}</td>
                                    <td>{{ $payment->paid_at->format('d-m-Y') }}</td>
                                    <td>{{ $payment->meterReading->reading_for_month->format('m-Y') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ number_format($payment->discount, 2) }}</td>
                                    <td class="fw-bold {{ $payment->remaining_after_payment > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($payment->remaining_after_payment, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <!-- Totals Footer (Print Only) -->
                        <tfoot class="print-table-footer d-none d-print-table-row-group">
                            <tr class="totals-row">
                                <td colspan="4">الإجمالي:</td>
                                <td>{{ number_format($payments->sum('amount'), 2) }} $</td>
                                <td>{{ number_format($payments->sum('discount'), 2) }} $</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-light border text-center shadow-sm rounded-3 py-5 flex-grow-1 d-flex flex-column justify-content-center">
                    <i class="bi bi-cash-coin display-4 mb-3 text-success mx-auto"></i>
                    <h5 class="text-muted fw-bold">لا توجد دفعات</h5>
                    <p class="text-muted mb-0">لم يتم العثور على أي عمليات دفع في هذه الفترة.</p>
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
                height: auto !important;
                overflow: visible !important;
            }

            #report-root {
                height: auto !important;
                overflow: visible !important;
                display: block !important;
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
                font-size: 14px !important;
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
