<?php

namespace App\Contracts\Admin\Reports;

use App\Data\Admin\Reports\ReportResult;
use Symfony\Component\HttpFoundation\Response;

/**
 * SRP: turning a built report into a downloadable Excel or PDF file.
 */
interface ReportExporterContract
{
    public function download(string $format, string $title, ReportResult $result): Response;
}
