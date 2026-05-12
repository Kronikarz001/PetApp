<?php

namespace App\Enums;

/**
 * Summary of PetStatusEnum
 */
enum PetStatusEnum: string
{
    case AVAILABLE = 'available';
    case PENDING   = 'pending';
    case SOLD      = 'sold';
}
