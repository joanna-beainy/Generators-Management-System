@if($showConfirmationModal && $pendingPaymentData)
<div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background: rgba(0,0,0,0.6); z-index: 9999;">
    
    <div class="card shadow-lg border-0 rounded-4" style="width: 450px; max-width: 90%;">
        <div class="card-header bg-success-subtle text-success text-center py-3 border-0 rounded-top-4">
            <h5 class="mb-0 fw-bold">تأكيد الدفعة الزائدة</h5>
        </div>
        <div class="card-body p-4 bg-white rounded-bottom-4" dir="rtl">
            <div class="text-center mb-4">
                <i class="bi bi-exclamation-circle text-warning display-4 mb-3 d-block"></i>
                <h6 class="text-dark fw-bold">المبلغ المدخل يتجاوز الرصيد المتبقي</h6>
                <p class="text-muted small">سيتم اعتبار المبلغ الزائد كرصيد للمشترك.</p>
            </div>
            
            <div class="bg-light rounded-4 p-3 mb-4 shadow-sm border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">الرصيد الحالي:</span>
                    <span class="fw-bold text-dark">{{ number_format($pendingPaymentData['current_remaining'], 2) }} $</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">المبلغ المدخل:</span>
                    <span class="fw-bold text-primary">{{ number_format($pendingPaymentData['total_payment'], 2) }} $</span>
                </div>
                <div class="border-top my-2"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">الفرق (زيادة):</span>
                    <span class="fw-bold text-warning h5 mb-0">{{ number_format($pendingPaymentData['overpayment'], 2) }} $</span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-success py-2 rounded-3 fw-bold shadow-sm" wire:click="confirmPayment">
                    تأكيد الدفعة
                </button>
                <button type="button" class="btn btn-link text-secondary text-decoration-none" wire:click="cancelPayment">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
</div>
@endif