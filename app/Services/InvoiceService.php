<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Str;
use TCPDF;

class InvoiceService
{
    public function generateInvoice(Payment $payment): Invoice
    {
        $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad(Invoice::count() + 1, 5, '0', STR_PAD_LEFT);

        $amount = $payment->amount;
        $discount = $payment->discount_amount ?? 0;
        $total = max(0, $amount - $discount);

        // Only apply course-level discount for full payments
        if ($discount <= 0 && $payment->course && $payment->course->discount_price && $payment->course->discount_price < $payment->course->price) {
            $discount = $payment->course->price - $payment->course->discount_price;
            $total = max(0, $amount - $discount);
        }

        return Invoice::create([
            'payment_id' => $payment->payment_id,
            'student_id' => $payment->student_id,
            'course_id' => $payment->course_id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'discount' => max(0, $discount),
            'tax' => 0,
            'total' => max(0, $total),
            'currency' => $payment->currency ?? 'ZMW',
            'payment_method' => $payment->payment_method ?? 'N/A',
            'description' => 'Payment for: ' . ($payment->course?->title ?? 'Course'),
            'invoice_date' => now(),
            'due_date' => null,
            'status' => 'paid',
        ]);
    }

    public function generatePdf(Invoice $invoice): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Invoice - ' . $invoice->invoice_number);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetFont('dejavuserif', '', 10);
        $pdf->AddPage();

        $data = $this->getInvoiceData($invoice);
        $html = view('invoices.pdf', $data)->render();
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }

    public function getInvoiceData(Invoice $invoice): array
    {
        $invoice->load(['student', 'course', 'payment']);

        return [
            'invoice' => $invoice,
            'student_name' => $invoice->student?->full_name ?? 'Unknown',
            'student_email' => $invoice->student?->email ?? '',
            'student_phone' => $invoice->student?->phone ?? '',
            'course_title' => $invoice->course?->title ?? 'Unknown Course',
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date?->format('F d, Y') ?? now()->format('F d, Y'),
            'amount' => $invoice->amount,
            'discount' => $invoice->discount,
            'tax' => $invoice->tax,
            'total' => $invoice->total,
            'currency' => $invoice->currency ?? 'ZMW',
            'payment_method' => ucfirst(str_replace('_', ' ', $invoice->payment_method ?? 'N/A')),
            'transaction_id' => $invoice->payment?->transaction_id ?? 'N/A',
            'description' => $invoice->description,
        ];
    }
}
