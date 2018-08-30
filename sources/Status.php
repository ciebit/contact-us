<?php
declare(strict_types=1);
namespace Ciebit\ContactUs;

use MyCLabs\Enum\Enum;

class Status extends Enum
{
    const DRAFT = 1;
    const ANALYZE = 2;
    const ACTIVE = 3;
    const TRASH = 4;
    const INACTIVE = 5;
}
