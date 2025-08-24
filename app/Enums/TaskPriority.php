<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function getList(): array
    {
        return [
            self::LOW,
            self::MEDIUM,
            self::HIGH
        ];
    }
}
