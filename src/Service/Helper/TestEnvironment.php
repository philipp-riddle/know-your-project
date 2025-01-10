<?php

namespace App\Service\Helper;

final class TestEnvironment
{
    /**
     * Returns whether the application is in test environment.
     * This is determined by the APP_ENV environment variable.
     * 
     * @return bool Whether the application is in test environment.
     */
    public static function isActive(): bool
    {
        return 'test' === $_ENV['APP_ENV'];
    }
}