<?php

namespace Filament\SpatieLaravelMediaLibraryPlugin\Forms;

use Spatie\MediaLibrary\HasMedia;

class ComponentContainerMixin
{
    public function mediaLibraryModel(): callable
    {
        return function (HasMedia $model): static {
            $this->meta['mediaLibraryModel'] = $model;

            return $this;
        };
    }

    public function getMediaLibraryModel(): callable
    {
        return function (): ?HasMedia {
            return $this->meta['mediaLibraryModel'] ?? $this->getParentComponent()?->getContainer()->getMediaLibraryModel() ?? null;
        };
    }
}