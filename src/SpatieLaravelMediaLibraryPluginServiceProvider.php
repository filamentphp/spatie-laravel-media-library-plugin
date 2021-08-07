<?php

namespace Filament\SpatieLaravelMediaLibraryPlugin;

use Filament\Forms2\ComponentContainer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpatieLaravelMediaLibraryPluginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-spatie-laravel-media-library-plugin');
    }

    public function packageBooted(): void
    {
        ComponentContainer::mixin(new Forms\ComponentContainerMixin());
    }
}