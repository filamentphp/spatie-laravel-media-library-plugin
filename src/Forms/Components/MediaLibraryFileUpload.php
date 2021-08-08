<?php

namespace Filament\SpatieLaravelMediaLibraryPlugin\Forms\Components;

use Filament\Forms2\Components\FileUpload;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use SplFileInfo;

class MediaLibraryFileUpload extends FileUpload
{
    protected $collection = null;

    protected $model = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);

        $this->hydrateStateUsing(function (MediaLibraryFileUpload $component, $state) {
            if ($component->isMultiple()) {
                return $state;
            }

            return $component->getUploadedFile();
        });
    }

    public function getUploadedFile()
    {
        $state = $this->getState();

        if ($state) {
            return $state;
        }

        $collection = $this->getCollection();
        $model = $this->getModel();

        if (! $collection) {
            return null;
        }

        if (! $model) {
            return null;
        }

        $media = $model
            ->getMedia($this->getCollection())
            ->first();

        if (! $media) {
            return null;
        }

        return $media->uuid;
    }

    public function mediaLibraryCollection(string | callable $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function mediaLibraryModel(HasMedia | callable $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getCollection(): ?string
    {
        if ($collection = $this->evaluate($this->collection)) {
            return $collection;
        }

        $containerParentComponent = $this->getContainer()->getParentComponent();

        if (! $containerParentComponent instanceof MultipleMediaLibraryFileUpload) {
            return null;
        }

        return $containerParentComponent->getCollection();
    }

    public function getModel(): ?HasMedia
    {
        if ($model = $this->evaluate($this->model)) {
            return $model;
        }

        if ($model = $this->getContainer()->getMediaLibraryModel()) {
            return $model;
        }

        $containerParentComponent = $this->getContainer()->getParentComponent();

        if (! $containerParentComponent instanceof MultipleMediaLibraryFileUpload) {
            return null;
        }

        return $containerParentComponent->getModel();
    }

    protected function handleUpload($file)
    {
        if (! ($model = $this->getModel())) {
            return $file;
        }

        $collection = $this->getCollection();

        $media = $model
            ->addMediaFromString($file->get())
            ->usingFileName($file->getFilename())
            ->toMediaCollection($collection);

        return $media->uuid;
    }

    protected function handleUploadedFileDeletion($file): void
    {
        if (! $file) {
            return;
        }

        Media::findByUuid($file)?->delete();
    }

    protected function handleUploadedFileRemoval($file): void
    {
        $this->deleteUploadedFile();

        $this->state(null);
    }

    protected function handleUploadedFileUrlRetrieval($file): ?string
    {
        if (! $this->getModel()) {
            return null;
        }

        if ($file instanceof SplFileInfo) {
            return null;
        }

        return Media::findByUuid($file)?->getUrl();
    }
}
