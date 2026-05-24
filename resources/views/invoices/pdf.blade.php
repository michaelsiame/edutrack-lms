<style>
body { font-family: Georgia, serif; color: #333; }
.header { border-bottom: 3px solid #1E3A8A; padding-bottom: 15px; margin-bottom: 20px; }
.header h1 { color: #1E3A8A; font-size: 24px; margin: 0; }
.header p { color: #666; font-size: 11px; margin: 3px 0; }
.invoice-title { font-size: 20px; color: #1E3A8A; margin: 20px 0 10px; }
.details-table { width: 100%; margin-bottom: 20px; }
.details-table td { padding: 5px 0; font-size: 11px; }
.details-table td:first-child { font-weight: bold; width: 30%; color: #1E3A8A; }
.items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
.items-table th { background: #1E3A8A; color: white; padding: 10px; text-align: left; font-size: 11px; }
.items-table td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 11px; }
.items-table .total-row { background: #f5f5f5; font-weight: bold; }
.footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #666; text-align: center; }
</style>

<div class="header">
    <h1>EDUTRACK COMPUTER TRAINING COLLEGE</h1>
    <p>Kalomo, Zambia | TEVETA Accredited | edutrackzambia@gmail.com</p>
</div>

<div class="invoice-title">INVOICE</div>

<table class="details-table">
    <tr><td>Invoice Number:</td><td>{{ $invoice_number }}</td></tr>
    <tr><td>Invoice Date:</td><td>{{ $invoice_date }}</td></tr>
    <tr><td>Status:</td><td><strong style="color: #059669;">PAID</strong></td></tr>
</table>

<div style="margin: 20px 0;">
    <strong style="color: #1E3A8A;">Bill To:</strong><br>
    {{ $student_name }}<br>
    {{ $student_email }}<br>
    {{ $student_phone }}
</div>

<table class="items-table">
    <thead>
        <tr>
            <th>Description</th>
            <th style="text-align: right;">Amount ({{ $currency }})</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $course_title }}<br><small>{{ $description }}</small></td>
            <td style="text-align: right;">{{ number_format($amount, 2) }}</td>
        </tr>
        @if($discount > 0)
        <tr>
            <td>Discount</td>
            <td style="text-align: right; color: #dc2626;">-{{ number_format($discount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td style="text-align: right;">TOTAL</td>
            <td style="text-align: right;">{{ number_format($total, 2) }}</td>
        </tr>
    </tbody>
</table>

<div style="margin: 20px 0; font-size: 11px;">
    <strong style="color: #1E3A8A;">Payment Details:</strong><br>
    Method: {{ $payment_method }}<br>
    Transaction Reference: {{ $transaction_id }}
</div>

<div class="footer">
    <p>Thank you for choosing Edutrack Computer Training College</p>
    <p>This is a computer-generated invoice and does not require a signature.</p>
</div>
