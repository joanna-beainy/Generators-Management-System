<div class="container mt-4" dir="rtl">
    <div class="d-flex justify-content-between mb-3">
        <input type="text" wire:model.debounce.500ms="search" class="form-control w-25" placeholder="بحث بالاسم...">
        <button wire:click="export" class="btn btn-outline-success">تصدير إلى Excel</button>
    </div>

    @if(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    @if(count($readings))
        <div class="table-responsive">
            <table class="table table-striped table-hover text-center align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>✓</th>
                        <th>الرقم</th>
                        <th>الاسم الكامل</th>
                        <th>الفئة</th>
                        <th>العداد السابق</th>
                        <th>العداد الحالي</th>
                        <th>الاستهلاك</th>
                        <th>المبلغ لهذا الشهر</th>
                        <th>الصيانة</th>
                        <th>المبلغ المتوجب</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($readings as $index => $reading)
                        <tr wire:key="row-{{ $reading->id }}">
                            <td>
                                @if(session("saved_{$reading->id}"))
                                    <span class="text-success fw-bold">✓</span>
                                @endif
                            </td>
                            <td>{{ $reading->client_id }}</td>
                            <td>{{ $reading->client->fullName() }}</td>
                            <td>{{ $reading->client->MeterCategory->category }}</td>
                            <td>{{ $reading->previous_meter }}</td>
                            <td>
                                <input type="number"
                                       id="meter-{{ $reading->id }}"
                                       wire:model.defer="readings.{{ $reading->id }}.current_meter"
                                       wire:blur="$emit('blurCurrentMeter', {{ $reading->id }}, $event.target.value)"
                                       wire:keydown.enter.prevent="$emit('blurCurrentMeter', {{ $reading->id }}, $event.target.value); $dispatch('focus-next-meter', { currentId: {{ $reading->id }} })"
                                       wire:keydown.arrow-down.prevent="$dispatch('focus-next-meter', { currentId: {{ $reading->id }} })"
                                       wire:keydown.arrow-up.prevent="$dispatch('focus-prev-meter', { currentId: {{ $reading->id }} })"
                                       class="form-control form-control-sm text-center">
                            </td>
                            <td>{{ $reading->consumption }}</td>
                            <td>{{ number_format($reading->amount, 2) }}</td>
                            <td>
                                <input type="number"
                                       wire:model.defer="readings.{{ $reading->id }}.maintenance_cost"
                                       wire:blur="$emit('blurMaintenanceCost', {{ $reading->id }}, $event.target.value)"
                                       class="form-control form-control-sm text-center">
                            </td>
                            <td>{{ number_format($reading->remaining_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $readings->links() }}
        </div>
    @else
        <h4 class="text-center mt-5">لا يوجد قراءات لهذا الشهر</h4>
    @endif
</div>

@push('scripts')
<script>
    window.addEventListener('focus-next-meter', event => {
        const ids = Array.from(document.querySelectorAll('[id^="meter-"]')).map(el => el.id.replace('meter-', ''));
        const currentIndex = ids.indexOf(event.detail.currentId.toString());
        const nextId = ids[currentIndex + 1];
        if (nextId) {
            const nextInput = document.getElementById(`meter-${nextId}`);
            nextInput?.focus();
            nextInput?.select();
        }
    });

    window.addEventListener('focus-prev-meter', event => {
        const ids = Array.from(document.querySelectorAll('[id^="meter-"]')).map(el => el.id.replace('meter-', ''));
        const currentIndex = ids.indexOf(event.detail.currentId.toString());
        const prevId = ids[currentIndex - 1];
        if (prevId) {
            const prevInput = document.getElementById(`meter-${prevId}`);
            prevInput?.focus();
            prevInput?.select();
        }
    });
</script>
@endpush
