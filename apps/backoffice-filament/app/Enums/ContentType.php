<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentType: string
{
    case News = 'NEWS';
    case Event = 'EVENT';
    case Exhibition = 'EXHIBITION';
    case Banner = 'BANNER';
}
