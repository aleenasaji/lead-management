<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'New';
    case Contacted = 'Contacted';
    case Converted = 'Converted';

    public static function options(): array
    {
        return array_map(
            static fn (self $status): array => [
                'value' => $status->value,
                'label' => $status->value,
            ],
            self::cases(),
        );
    }
}
