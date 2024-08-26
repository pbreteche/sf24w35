<?php

namespace App\Entity\Enum;

enum IssueState: string
{
    case open = 'OPEN';
    case closed = 'CLOSED';
}
