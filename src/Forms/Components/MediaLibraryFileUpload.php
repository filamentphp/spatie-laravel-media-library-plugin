<?php

namespace Filament\SpatieLaravelMedialibraryPlugin\Forms\Components;

use Filament\Forms2\Components\FileUpload;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibraryFileUpload extends FileUpload
{
    protected $mediaLibraryCollection = null;

    protected $mediaLibraryModel = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);

        $this->hydrateStateUsing(function () {
            return $this->getUploadedFile();
        });
    }

    public function getUploadedFile()
    {
        $state = $this->getState();

        if ($state) {
            return $state;
        }

        if (! ($model = $this->getMediaLibraryModel())) {
            return null;
        }

        $media = $model
            ->getMedia($this->getMediaLibraryCollection())
            ->first();

        if (! $media) {
            return null;
        }

        return $media->uuid;
    }

    public function mediaLibraryCollection(string | callable $collection): static
    {
        $this->mediaLibraryCollection = $collection;

        return $this;
    }

    public function mediaLibraryModel(HasMedia | callable $model): static
    {
        $this->mediaLibraryModel = $model;

        return $this;
    }

    public function removeUploadedFile(): static
    {
        $this->deleteUploadedFile();

        return $this->state(null);
    }

    public function getMediaLibraryCollection(): ?string
    {
        return $this->evaluate($this->mediaLibraryCollection);
    }

    public function getMediaLibraryModel(): ?HasMedia
    {
        return $this->evaluate($this->mediaLibraryModel) ?? $this->getContainer()->getMediaLibraryModel() ?? null;
    }

    protected function handleUpload($file)
    {
        if (! ($model = $this->getMediaLibraryModel())) {
            return $file;
        }

        $collection = $this->getMediaLibraryCollection();

        $media = $model
            ->addMediaFromString($file->get())
            ->usingFileName($file->getFilename())
            ->toMediaCollection($collection);

        return $media->uuid;
    }

    protected function handleUploadedFileDeletion($file): void
    {
        if (! $file) return;

        Media::findByUuid($file)?->delete();
    }

    protected function handleUploadedFileUrlRetrieval($file): ?string
    {
        if (! $this->getMediaLibraryModel()) {
            return null;
        }

        if ($file instanceof SplFileInfo) {
            return null;
        }

        return Media::findByUuid($file)?->getUrl();
    }
}
