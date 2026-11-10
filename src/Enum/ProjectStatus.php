<?php

namespace App\Enum;

enum ProjectStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'À faire',
            self::InProgress => 'En cours',
            self::OnHold => 'En pause',
            self::Done => 'Terminé',
        };
    }
}
