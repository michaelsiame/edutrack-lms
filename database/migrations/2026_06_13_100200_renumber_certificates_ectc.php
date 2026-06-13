<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalise EVERY certificate number to ECTC{yy}{seq}, year-scoped by issue
 * date, in issue order. Two-pass to avoid transient clashes with the unique
 * index (uk_cert_number): pass 1 parks every row on a temp value, pass 2
 * assigns the final sequential numbers. Aligns with the live generator so
 * future issues continue the sequence.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $certs = DB::table('certificates')
                ->orderBy('issued_date')
                ->orderBy('certificate_id')
                ->get();

            // Pass 1: park on unique temp values so finals can't collide.
            foreach ($certs as $cert) {
                DB::table('certificates')
                    ->where('certificate_id', $cert->certificate_id)
                    ->update(['certificate_number' => 'TMP-' . $cert->certificate_id]);
            }

            // Pass 2: assign final ECTC{yy}{seq}, sequence reset per year.
            $seqByYear = [];
            foreach ($certs as $cert) {
                $dateStr = $cert->issued_date ?: ($cert->created_at ?: date('Y-m-d'));
                $yy = date('y', strtotime($dateStr));
                $seqByYear[$yy] = ($seqByYear[$yy] ?? 0) + 1;
                $number = 'ECTC' . $yy . str_pad((string) $seqByYear[$yy], 3, '0', STR_PAD_LEFT);

                DB::table('certificates')
                    ->where('certificate_id', $cert->certificate_id)
                    ->update(['certificate_number' => $number]);
            }
        });
    }

    public function down(): void
    {
        // Identity values; not reversible.
    }
};
