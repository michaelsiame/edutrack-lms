<?php

namespace App\Observers;

use App\Models\Certificate;

class CertificateObserver
{
    public function saved(Certificate $certificate): void
    {
        StudentObserver::syncCounters($certificate->user_id);
    }

    public function deleted(Certificate $certificate): void
    {
        StudentObserver::syncCounters($certificate->user_id);
    }
}
