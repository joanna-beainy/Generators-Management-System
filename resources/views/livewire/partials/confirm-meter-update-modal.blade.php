<div x-data="{
         showConfirmModal: false,
         confirmReadingId: null,
         confirmOldMeter: null,
         confirmNewMeter: null
     }"
     x-show="showConfirmModal"
     x-cloak
     style="background: rgba(0,0,0,.5); position: fixed; inset: 0; z-index: 1050;"
     x-init="
        window.addEventListener('show-confirm-modal', (e) => {
            confirmReadingId = e.detail.readingId;
            confirmOldMeter = e.detail.oldMeter;
            confirmNewMeter = e.detail.value;
            showConfirmModal = true;
        });
     ">
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        تأكيد تعديل قراءة العداد
                    </h5>
                    <!-- hide immediately on client -->
                    <button type="button" class="btn-close" @click="showConfirmModal = false"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="mb-3">
                        لقد تم إدخال قراءة لهذا العداد مسبقاً.
                    </p>

                    <div class="row mb-3">
                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted">القراءة السابقة</small>
                                <div class="fw-bold text-danger fs-5" x-text="confirmOldMeter"></div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <small class="text-muted">القراءة الجديدة</small>
                                <div class="fw-bold text-success fs-5" x-text="confirmNewMeter"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small">
                        هل أنت متأكد أنك تريد تعديل القراءة؟
                    </p>
                </div>

                <div class="modal-footer justify-content-between">
                    <!-- hide immediately on client -->
                    <button class="btn btn-outline-secondary"
                            @click="showConfirmModal = false">
                        إلغاء
                    </button>

                    <!-- hide modal client-side, then call server with params -->
                    <button class="btn btn-danger"
                            @click="showConfirmModal = false; $nextTick(() => $wire.call('confirmMeterUpdate', confirmReadingId, confirmNewMeter))">
                        نعم، تعديل القراءة
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>