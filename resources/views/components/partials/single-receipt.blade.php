{{-- Single Receipt Content - Optimized for consistent modal and print rendering --}}
<div class="single-receipt-content" 
     style="width: 100%; height: 100%; display: flex; flex-direction: column; font-family: 'Arial', sans-serif; font-size: 13px; line-height: 1.2; justify-content: space-between; box-sizing: border-box;" 
     dir="rtl">
    
    {{-- Header Section --}}
    <div style="text-align: center; margin-bottom: 2px;">
        <p style="margin: 0; font-weight: bold; font-size: 18px;">
            {{ $receiptData['user_name'] }}
        </p>
        <p style="margin: 0; font-size: 14px;">
            {{ $receiptData['user_phones'] }}
        </p>
    </div>

    {{-- Client Information --}}
    <div style="margin-bottom: 4px; line-height: 1.3; font-size: 14px; font-weight: bold;">
        <div>الرقم: {{ $receiptData['client_id'] }}</div>
        <div>الأسم: {{ $receiptData['client_full_name'] }}</div>
        <div>عن شهر: {{ $receiptData['reading_for_month_arabic'] }}</div>
    </div>

    {{-- Meter Readings Table --}}
    <table style=" width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 14px; font-weight: bold;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 2px; text-align: center;">
                    <div style="display: flex; justify-content: space-around; align-items: center;">
                        <span>العداد السابق:</span>
                        <span>{{ $receiptData['previous_meter'] }}</span>
                    </div>
                </th>
                <th style="border: 1px solid #000; padding: 2px; text-align: center;">
                    <div style="display: flex; justify-content: space-around; align-items: center;">
                        <span>العداد الحالي:</span>
                        <span>{{ $receiptData['current_meter'] }}</span>
                    </div>
                </th>
                <th style="border: 1px solid #000; padding: 2px; text-align: center;">
                    <div style="display: flex; justify-content: space-around; align-items: center;">
                        <span>الاستهلاك:</span>
                        <span>{{ $receiptData['consumption'] }}</span>
                    </div>
                </th>
            </tr>
        </thead>
    </table>

    {{-- Charges Breakdown Table --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px; font-size: 14px; font-weight: bold;">
        <tbody>
            <tr>
                <th style="padding: 1px; text-align: right;">
                    <strong style="padding-left: 100px;">الاشتراك:</strong> {{ $receiptData['meter_category'] }}
                </th>
                <th style="padding: 1px;"></th>
                <th style="padding: 1px; text-align: right;">{{ number_format($receiptData['meter_category_price'], 2) }}$</th>
            </tr>
            <tr>
                <th style="padding: 1px; width: 50%; text-align: right;">
                    <strong style="padding-left: 90px;">الاستهلاك:</strong>  {{ $receiptData['consumption'] }} K.W 
                </th>
                <th style="padding: 1px; text-align: right;">
                    سعر الكيلو: {{ number_format($receiptData['kilowatt_price'], 2) }}$
                </th>
                <th style="padding: 1px; text-align: right;">{{ number_format($receiptData['consumption_amount'], 2) }}$</th>
            </tr>
            <tr>
                <th style="padding: 1px; text-align: right;">الصيانة:</th>
                <th style="padding: 1px;"></th>
                <th style="padding: 1px; text-align: right;">{{ number_format($receiptData['maintenance_cost'], 2) }}$</th>
            </tr>
            <tr>
                <th style="padding: 1px; text-align: right;">الرصيد السابق:</th>
                <th style="padding: 1px;"></th>
                <th style="padding: 1px; text-align: right;">{{ number_format($receiptData['previous_balance'], 2) }}$</th>
            </tr>
            <tr>
                <th style="padding: 1px; border-top: 1px solid #000; text-align: right; font-size: 15px; ">
                    <strong>المجموع:</strong> 
                </th>
                <th style="padding: 1px; border-top: 1px solid #000;"></th>
                <th style="padding: 1px; border-top: 1px solid #000; text-align: right; font-size: 15px;">
                    <strong>{{ number_format($receiptData['total_due'], 2) }}$</strong>
                </th>
            </tr>
        </tbody>
    </table>

    {{-- Payment Summary Table --}}
    <table style="width: 50%; border-collapse: collapse; margin-bottom: 4px;">
        <tbody>
            <tr>
                <th style="border: 1px solid #000; padding: 2px; font-weight: bold; font-size: 14px;">
                    <div style="display: flex; justify-content: space-around; align-items: center;">
                        <strong>المبلغ المدفوع</strong>
                        <strong>{{ number_format($receiptData['amount_paid'], 2) }}$</strong>
                    </div>
                </th>
            </tr>
            <tr>
                <th style="border: 1px solid #000; padding: 2px; font-weight: bold; font-size: 14px;">
                    <div style="display: flex; justify-content: space-around; align-items: center;">
                        <strong>الرصيد المتبقي</strong>
                        <strong>{{ number_format($receiptData['remaining_after_payment'], 2) }}$</strong>
                    </div>
                </th>
            </tr>
        </tbody>
    </table>

    {{-- Footer Section --}}
    <div style="font-size: 13px; line-height: 1.2;">
        <div style="margin-bottom: 2px; text-align: right; font-weight: bold;">
            يرجى تسديد المبلغ المتوجب قبل اليوم التاسع من الشهر حفاظا على استمرارية العمل
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
            <div><strong>التاريخ:</strong> {{ $receiptData['payment_date'] }}</div>
            <div><strong>توقيع المستلم:</strong> ______________</div>
        </div>
    </div>
</div>
