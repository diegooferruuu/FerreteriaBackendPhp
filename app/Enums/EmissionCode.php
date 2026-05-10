<?php

namespace App\Enums;

enum EmissionCode: int
{
    case ONLINE = 1;
    case OFFLINE = 2;
    case MASSIVE = 3;
}
