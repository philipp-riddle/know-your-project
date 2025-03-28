<?php

namespace App\Service\Integration;

enum MercureEntityEvent: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case ORDER = 'order';
    case DELETE = 'delete';

    public static function getName(MercureEntityEvent $case): string
    {
        return match ($case) {
            self::CREATE => 'create',
            self::UPDATE => 'update',
            self::ORDER => 'order',
            self::DELETE => 'delete',
        };
    }
}