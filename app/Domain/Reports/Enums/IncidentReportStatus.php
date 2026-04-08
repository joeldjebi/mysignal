<?php

namespace App\Domain\Reports\Enums;

enum IncidentReportStatus: string
{
    case Submitted = 'submitted';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Rejected = 'rejected';
}
