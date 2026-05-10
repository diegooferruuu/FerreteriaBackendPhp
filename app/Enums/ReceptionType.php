<?php

namespace App\Enums;

enum ReceptionType: string
{
    case INDIVIDUAL = 'INDIVIDUAL';
    case CONTINGENCY = 'CONTINGENCIA';
    case MASSIVE = 'MASIVA';
}