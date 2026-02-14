<div>
    @if($isOpen)
    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
         style="background: rgba(0,0,0,0.6); z-index: 9999;">
        
        <div class="card shadow-lg border-0 rounded-4" style="width: 400px; max-width: 90%;">
            <div class="card-header bg-success bg-opacity-10 text-white text-center py-3 border-0 rounded-top-4">
                <h5 class="mb-0 fw-bold text-success">تأكيد كلمة السر</h5>
            </div>
            <div class="card-body p-4 bg-white rounded-bottom-4" dir="rtl">
                <p class="text-secondary text-center mb-4">يرجى إدخال كلمة السر الخاصة بك للمتابعة .</p>
                
                <form wire:submit.prevent="verify">
                    <div class="mb-3" x-data="{}" x-init="setTimeout(() => $refs.pwd.focus(), 100)">
                        <input type="password" 
                               wire:model="password" 
                               x-ref="pwd"
                               class="form-control text-center py-2 @error('password') is-invalid @enderror" 
                               placeholder="كلمة السر">
                               @error('password') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success py-2 rounded-3 fw-bold" wire:loading.attr="disabled">
                            <span wire:loading.remove>تأكيد</span>
                            <div wire:loading class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                        <button type="button" wire:click="close" class="btn btn-link text-secondary text-decoration-none" wire:loading.attr="disabled">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
