<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planned => '読書予定',
            self::Completed => '読了',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Planned => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
        };
    }
}
