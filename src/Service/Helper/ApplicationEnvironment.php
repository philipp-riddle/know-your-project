<?php

namespace App\Service\Helper;

final class ApplicationEnvironment
{
    /**
     * Returns whether the application is in test environment.
     * This is determined by the APP_ENV environment variable.
     * 
     * @return bool Whether the application is in test environment.
     */
    public static function isTestEnv(): bool
    {
        return 'test' === $_ENV['APP_ENV'];
    }

    /**
     * Returns whether the application is in dev environment.
     * This is determined by the APP_ENV environment variable.
     * 
     * @return bool Whether the application is in dev environment.
     */
    public static function isDevEnv(): bool
    {
        return 'dev' === $_ENV['APP_ENV'];
    }

    /**
     * Returns whether the application is in prod environment.
     * This is determined by the APP_ENV environment variable.
     * 
     * @return bool Whether the application is in prod environment.
     */
    public static function isProdEnv(): bool
    {
        return 'prod' === $_ENV['APP_ENV'];
    }
}