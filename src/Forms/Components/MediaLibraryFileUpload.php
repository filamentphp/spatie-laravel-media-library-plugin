<?php

namespace Filament\SpatieLaravelMediaLibraryPlugin\Forms\Components;

use Filament\Forms2\Components\FileUpload;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use SplFileInfo;

class MediaLibraryFileUpload extends FileUpload
{
    protected $collection = null;

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

        if (! ($collection && $model)) {
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

    public function saveRelationships(): void
    {
        $this->saveUploadedFile();
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

    protected function handleUpload($file)
    {
        if (! ($model = $this->getModel())) {
            return $file;
        }

        $media = $model
            ->addMediaFromString($file->get())
            ->usingFileName($file->getFilename())
            ->toMediaCollection($this->getCollection());

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
