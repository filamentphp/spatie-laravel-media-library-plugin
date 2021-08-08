<?php

namespace Filament\SpatieLaravelMediaLibraryPlugin\Forms;

use Filament\SpatieLaravelMediaLibraryPlugin\Forms\Components\MultipleMediaLibraryFileUpload;
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
            if ($model = $this->meta['mediaLibraryModel'] ?? null) {
                return $model;
            }

            $parentComponent = $this->getParentComponent();

            if (! $parentComponent) {
                return null;
            }

            if (
                $parentComponent instanceof MultipleMediaLibraryFileUpload &&
                ($model = $parentComponent->getModel())
            ) {
                return $model;
            }

            return $parentComponent->getContainer()->getMediaLibraryModel();
        };
    }
}
