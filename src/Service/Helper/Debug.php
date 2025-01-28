<?php

namespace App\Service\Helper;

namespace App\Service\Helper;

/**
 * Little class to help us with debugging messages across the whole application.
 * Debugging messages can be especially helpful in the console output when running commands, e.g. the entity embedding queue command.
 */
final class Debug
{
    private static bool $isEnabled = false;

    public static function enable(): void
    {
        self::$isEnabled = true;
    }

    public static function disable(): void
    {
        self::$isEnabled = false;
    }

    public static function print(string $message, ?string $category = null): void
    {
        if (!self::$isEnabled) {
            return;
        }

        $category ??= 'debug';

        \printf('[%s][%s] %s' . PHP_EOL, (new \DateTime())->format('Y-m-d H:i:s'), $category, $message);
    }
}