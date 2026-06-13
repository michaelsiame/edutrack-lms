<?php

namespace App\Pdf;

use TCPDF;

class EdutrackPdf extends TCPDF
{
    /**
     * TCPDF::Close() emits an invisible "Powered by TCPDF" link unless the
     * protected $tcpdflink flag is off. The flag cannot be set from outside
     * the class, and TCPDF's constructor forces it to true, so it must be
     * cleared after parent construction.
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->tcpdflink = false;
    }
}
