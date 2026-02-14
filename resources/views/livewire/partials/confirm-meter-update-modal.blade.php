<div x-data="{
         showConfirmModal: false,
         confirmReadingId: null,
         confirmOldMeter: null,
         confirmNewMeter: null
     }"
     x-show="showConfirmModal"
     x-cloak
     style="background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); position: fixed; inset: 0; z-index: 1050;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-init="
        window.addEventListener('show-confirm-modal', (e) => {
            confirmReadingId = e.detail.readingId;
            confirmOldMeter = e.detail.oldMeter;
            confirmNewMeter = e.detail.value;
            showConfirmModal = true;
        });
     ">
    <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                <div class="modal-header bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25 pb-3">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        تأكيد تعديل قراءة العداد
                    </h5>
                    <button type="button" class="btn-close shadow-none" @click="showConfirmModal = false"></button>
                </div>

                <div class="modal-body text-center p-4">
                    <p class="text-secondary mb-4 fs-5">
                        لقد تم إدخال قراءة لهذا العداد مسبقاً.
                    </p>

                    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                        <!-- Old Meter Card -->
                        <div class="card border border-danger border-opacity-25 bg-danger bg-opacity-10 rounded-4 shadow-sm" style="width: 140px;">
                            <div class="card-body p-3">
                                <small class="text-muted d-block mb-1">القراءة السابقة</small>
                                <div class="fw-bold text-danger fs-4 font-monospace" x-text="confirmOldMeter"></div>
                            </div>
                        </div>

                        <i class="bi bi-arrow-left text-secondary fs-4 opacity-50"></i>

                        <!-- New Meter Card -->
                        <div class="card border border-success border-opacity-25 bg-success bg-opacity-10 rounded-4 shadow-sm" style="width: 140px;">
                            <div class="card-body p-3">
                                <small class="text-muted d-block mb-1">القراءة الجديدة</small>
                                <div class="fw-bold text-success fs-4 font-monospace" x-text="confirmNewMeter"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        هل أنت متأكد أنك تريد تعديل القراءة؟
                    </p>
                </div>

                <div class="modal-footer border-0 p-4 bg-light bg-opacity-50 justify-content-between">
                    <button class="btn btn-outline-secondary rounded-pill px-4"
                            @click="showConfirmModal = false">
                        <i class="bi bi-x-circle me-2"></i>إلغاء
                    </button>

                    <button class="btn btn-danger rounded-pill px-4 shadow-sm"
                            @click="showConfirmModal = false; $nextTick(() => $wire.call('confirmMeterUpdate', confirmReadingId, confirmNewMeter))">
                        <i class="bi bi-check-circle me-2"></i>نعم، تعديل القراءة
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>