<?php

namespace App\Enum;

enum LogAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case AttachmentAdded = 'attachment_added';
    case AttachmentRemoved = 'attachment_removed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Création',
            self::Updated => 'Modification',
            self::Deleted => 'Suppression',
            self::AttachmentAdded => 'Fichier ajouté',
            self::AttachmentRemoved => 'Fichier supprimé',
        };
    }
}
