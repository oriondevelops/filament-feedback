<?php

namespace Orion\FilamentFeedback;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FeedbackPluginServiceProvider extends PackageServiceProvider
{
    public static string $name = 'feedback';

    public static string $viewNamespace = 'feedback';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(static::$name)
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }
}
