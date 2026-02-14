<div class="container mt-2" dir="rtl" x-data>
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold text-dark mb-0">
                <i class="bi bi-clipboard-data-fill text-success me-2"></i> تقرير شهري لقراءة العدادات
            </h3>
            <p class="text-secondary mb-0 mt-1">
                <i class="bi bi-calendar3 me-1"></i> عن شهر {{ $arabicMonthName }} {{ $selectedYear }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" 
                    class="btn btn-success rounded-pill shadow-sm px-4"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i> طباعة النموذج
            </button>
            <a href="{{ route('users.dashboard') }}" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                <i class="bi bi-x-circle me-1"></i> إغلاق
            </a>
        </div>
    </div>

    <!-- Alpine.js Auto-Disappearing Alert -->
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

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4">

            <!-- Printable Header -->
            <div class="print-header text-center mb-3 d-none d-print-block">
                <h6 class="fw-bold mb-1">تقرير شهري لقراءة العدادات</h6>
                <div>عن شهر {{ $arabicMonthName }} {{ $selectedYear }}</div>
            </div>

            @if(count($clients))
                <div class="table-responsive rounded-3 border" style="max-height: 68vh; overflow-y: auto;">
                    <table class="table table-hover text-center align-middle mb-0" id="report-table">
                        <thead class="table-secondary print-table-header" style="position: sticky; top: 0; z-index: 1;">
                            <tr class="text-uppercase small fw-bold">
                                <th style="width: 100px;">رقم المشترك</th>
                                <th style="width: 200px;">اسم المشترك</th>
                                <th>العداد السابق</th>
                                <th>العداد الحالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                <tr>
                                    <td>{{ $client['id'] }}</td>
                                    <td>{{ $client['full_name'] }}</td>
                                    <td>{{ $client['previous_meter'] }}</td>
                                    <td class="current-meter-cell"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-speedometer2 display-4 text-success mb-3"></i>
                    <h5 class="text-muted">لا يوجد قراءات</h5>
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

            /* Remove layout headers/footers */
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

            .print-header {
                display: block !important;
                margin-bottom: 8px;
            }

            .current-meter-cell {
                min-height: 25px;
            }

            /* Ensure proper page breaks */
            tr {
                page-break-inside: avoid;
            }
        }

        @media screen {
            .current-meter-cell {
                border: 1px dashed #ccc !important;
                background: #f8f9fa !important;
                min-height: 35px;
            }

            .table-responsive {
                /* ensure the scrolling context for sticky */
                max-height: 70vh;
                overflow: auto;
            }

            .table-responsive thead th {
                position: sticky;
                top: 0;
                background: #e6e6e6; 
                z-index: 5;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
        }
    </style>
</div>