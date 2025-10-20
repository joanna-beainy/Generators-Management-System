@if($showConfirmationModal && $pendingPaymentData)
<div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    تأكيد الدفعة الزائدة
                </h5>
                <button type="button" class="btn-close" wire:click="cancelPayment"></button>
            </div>
            <div class="modal-body py-4" dir="rtl">
                <div class="text-center mb-3">
                    <i class="fas fa-info-circle text-primary fa-2x mb-3"></i>
                    <h6 class="text-dark mb-3">المبلغ المدخل يتجاوز الرصيد المتبقي</h6>
                </div>
                
                <div class="bg-light rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">الرصيد الحالي:</span>
                        <span class="fw-bold text-dark">{{ number_format($pendingPaymentData['current_remaining'], 2) }} $</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">المبلغ المدخل:</span>
                        <span class="fw-bold text-primary">{{ number_format($pendingPaymentData['total_payment'], 2) }} $</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">الفرق (زيادة):</span>
                        <span class="fw-bold text-warning">{{ number_format($pendingPaymentData['overpayment'], 2) }} $</span>
                    </div>
                </div>

                <p class="text-muted text-center small mb-0">
                    <i class="fas fa-lightbulb text-info me-1"></i>
                    سيتم اعتبار المبلغ الزائد كرصيد مدين للعميل وسيخصم من الفاتورة القادمة.
                </p>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" wire:click="cancelPayment">
                    <i class="fas fa-times me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-primary" wire:click="confirmPayment">
                    <i class="fas fa-check me-1"></i> تأكيد الدفعة
                </button>
            </div>
        </div>
    </div>
</div>
@endif