<?php

namespace App\Pdf;

use setasign\Fpdi\Tcpdf\Fpdi;

class EdutrackFpdi extends Fpdi
{
    /**
     * TCPDF::Close() emits an invisible "Powered by TCPDF" link unless the
     * protected $tcpdflink flag is off. TCPDF's constructor forces it to
     * true, so it must be cleared after parent construction.
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->tcpdflink = false;
    }
}
