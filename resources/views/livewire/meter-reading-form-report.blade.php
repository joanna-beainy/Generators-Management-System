<div class="container mt-4" dir="rtl" x-data>
    <div class="card shadow-sm">
        <div class="card-header bg-light text-dark no-print">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data-fill text-success me-2"></i>
                        تقرير شهري لقراءة العدادات
                    </h5>
                    <small class="text-muted">عن شهر {{ $arabicMonthName }} {{ $selectedYear }}</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" 
                            class="btn btn-success btn-sm"
                            onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>
                        طباعة النموذج
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
            
            <!-- Printable Header -->
            <div class="print-header text-center mb-3 d-none d-print-block">
                <h6 class="fw-bold mb-1">تقرير شهري لقراءة العدادات</h6>
                <div>عن شهر {{ $arabicMonthName }} {{ $selectedYear }}</div>
            </div>

            @if(count($clients))
                <div class="table-responsive" >
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light print-table-header">
                            <tr>
                                <th>رقم المشترك</th>
                                <th>اسم المشترك</th>
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
                    <i class="bi bi-people display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">لا يوجد مشتركين </h5>
                    <p class="text-muted">لا توجد مشتركين حتى الآن</p>
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
                background: #e6e6e6; /* match thead bg */
                z-index: 5;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
        }
    </style>
</div>