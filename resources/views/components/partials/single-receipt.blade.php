<div class="single-receipt-content" style="width: 100%; height: 100%; display: flex; flex-direction: column; font-family: 'Arial', sans-serif; font-size: 9px; line-height: 1.3; justify-content: space-between;" dir="rtl">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 4px;">
        <h4 style="margin: 0; font-size: 11px; font-weight: bold;">
            {{ $receiptData['user_name'] }}
        </h4>
        <p style="margin: 0; font-size: 8px;">
            {{ $receiptData['user_phones'] }}
        </p>
    </div>

    <!-- Client Info -->
    <div style="margin-bottom: 4px; line-height: 1.4;">
        <div><strong>الرقم:</strong> {{ $receiptData['client_id'] }}</div>
        <div><strong> الأسم:</strong> {{ $receiptData['client_full_name'] }}</div>
        <div><strong>عن شهر:</strong> {{ $receiptData['reading_for_month_arabic'] }}</div>
    </div>

    <!-- Consumption Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8px; text-align: center;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 3px;">العداد السابق</th>
                <th style="border: 1px solid #000; padding: 3px;">العداد الحالي</th>
                <th style="border: 1px solid #000; padding: 3px;">الاستهلاك</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 3px; aligne-text">{{ number_format($receiptData['previous_meter']) }} </td>
                <td style="border: 1px solid #000; padding: 3px;">{{ number_format($receiptData['current_meter']) }} </td>
                <td style="border: 1px solid #000; padding: 3px;">{{ number_format($receiptData['consumption']) }} </td>
            </tr>
        </tbody>
    </table>

    <!-- Charges Section -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8px;">
        <tbody>
            <tr>
                <td style="padding: 2px;"><strong>الاشتراك:</strong> {{ $receiptData['meter_category'] }}</td>
                <td style="padding: 2px;">{{ $receiptData['meter_category'] }}</td>
                <td style="padding: 2px;">{{ number_format($receiptData['meter_category_price'], 2) }}$</td>
            </tr>
            <tr>
                <td style="padding: 2px;"><strong>الاستهلاك:</strong> {{ number_format($receiptData['consumption']) }} K.W</td>
                <td style="padding: 2px;"><strong>سعر الكيلو: </strong> {{number_format($receiptData['kilowatt_price'], 2)}} $</td>
                <td style="padding: 2px;">{{ number_format($receiptData['consumption_amount'], 2) }}$</td>
            </tr>
            <tr>
                <td style="padding: 2px;"><strong>الصيانة:</strong></td>
                <td style="padding: 2px;"></td>
                <td style="padding: 2px;">{{ number_format($receiptData['maintenance_cost'], 2) }}$</td>
            </tr>
            <tr>
                <td style="padding: 2px;"><strong>الرصيد السابق:</strong></td>
                <td style="padding: 2px;"></td>
                <td style="padding: 2px;">{{ number_format($receiptData['previous_balance'], 2) }}$</td>
            </tr>
            <tr style="font-weight: bold;">
                <td style="padding: 2px;"><strong>المجموع:</strong></td>
                <td style="padding: 2px;"><strong>ما يعادل بالليرة اللبنانية:</strong>{{ number_format($receiptData['total_due_lbp'], 0) }} ل.ل</td>
                <td style="padding: 2px;">{{ number_format($receiptData['total_due'], 2) }}$</td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Summary -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8px;">
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 3px;">المبلغ المدفوع</td>
                <td style="border: 1px solid #000; padding: 3px;">{{ number_format($receiptData['amount_paid'], 2) }}$</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 3px;">الرصيد المتبقي</td>
                <td style="border: 1px solid #000; padding: 3px;">{{ number_format($receiptData['remaining_after_payment'], 2) }}$</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div style="font-size: 7px; line-height: 1.3;">
        <div style="margin-bottom: 3px; text-align: right;">
            <strong>يرجى تسديد المبلغ المتوجب قبل اليوم التاسع من الشهر حفاظا على استمرارية العمل</strong>
        </div>
        <div style="margin-bottom: 3px; text-align: right;">
            <strong>توقيع المستلم:</strong> ______________
        </div>
        <div style="text-align: center; margin-bottom: 2px;">
            <strong>التاريخ:</strong> {{$receiptData['payment_date']}}
        </div>
        <div style="text-align: center; font-size: 6px; color: #666;">
            PAY-{{ $receiptData['payment_id'] }}-{{ $copyType }}
        </div>
    </div>
</div>