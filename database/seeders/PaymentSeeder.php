<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [
            [
                'payment_id' => 1,
                'student_id' => 4,
                'course_id' => 1,
                'enrollment_id' => 1,
                'amount' => 1500.00,
                'currency' => 'ZMW',
                'payment_type' => 'course_fee',
                'payment_status' => 'Completed',
                'transaction_id' => 'LENCO-001',
                'phone_number' => '+260770000004',
                'payment_date' => '2024-01-15 10:30:00',
            ],
            [
                'payment_id' => 2,
                'student_id' => 4,
                'course_id' => 2,
                'enrollment_id' => 2,
                'amount' => 500.00,
                'currency' => 'ZMW',
                'payment_type' => 'partial_payment',
                'payment_status' => 'Completed',
                'transaction_id' => 'LENCO-002',
                'phone_number' => '+260770000004',
                'payment_date' => '2024-02-01 14:00:00',
            ],
            [
                'payment_id' => 3,
                'student_id' => 5,
                'course_id' => 1,
                'enrollment_id' => 3,
                'amount' => 1500.00,
                'currency' => 'ZMW',
                'payment_type' => 'course_fee',
                'payment_status' => 'Completed',
                'transaction_id' => 'LENCO-003',
                'phone_number' => '+260770000005',
                'payment_date' => '2023-11-01 09:15:00',
            ],
        ];

        foreach ($payments as $payment) {
            DB::table('payments')->updateOrInsert(['payment_id' => $payment['payment_id']], $payment);
        }
    }
}
