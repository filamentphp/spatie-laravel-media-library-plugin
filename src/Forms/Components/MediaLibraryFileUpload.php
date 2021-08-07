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
    }

    public function deleteUploadedFile(): static
    {
        $file = $this->getState();

        if ($callback = $this->deleteUploadedFileUsing) {
            $this->evaluate($callback, [
                'file' => $file,
            ]);
        } else {
            Media::findByUuid($file)?->delete();
        }

        return $this;
    }

    public function getUploadedFile()
    {
        if (! ($state = $this->getState())) {
            $state = $this->getUploadedFileFromMediaLibrary();
        }

        return $state;
    }

    public function getUploadedFileFromMediaLibrary(): ?string
    {
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

    public function getUploadedFileUrl(): ?string
    {
        if ($callback = $this->getUploadedFileUrlUsing) {
            return $this->evaluate($callback);
        }

        return $this->getUploadedFileUrlFromMediaLibrary();
    }

    public function getUploadedFileUrlFromMediaLibrary(): ?string
    {
        if (! $this->getMediaLibraryModel()) {
            return null;
        }

        if (! ($mediaUuid = $this->getState())) {
            return null;
        }

        if ($mediaUuid instanceof SplFileInfo) {
            return null;
        }

        return Media::findByUuid($mediaUuid)?->getUrl();
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

    public function saveUploadedFile()
    {
        if ($callback = $this->saveUploadedFileUsing) {
            return $this->evaluate($callback);
        }

        return $this->saveUploadedFileToMediaLibrary();
    }

    public function saveUploadedFileToMediaLibrary()
    {
        $file = $this->getState();

        if (! ($model = $this->getMediaLibraryModel())) {
            return $file;
        }

        $collection = $this->getMediaLibraryCollection();

        $media = $model
            ->addMediaFromString($file->get())
            ->usingFileName($file->getFilename())
            ->toMediaCollection($collection);

        $this->state($media->uuid);

        return $media->uuid;
    }

    public function getMediaLibraryCollection(): ?string
    {
        return $this->evaluate($this->mediaLibraryCollection);
    }

    public function getMediaLibraryModel(): ?HasMedia
    {
        return $this->evaluate($this->mediaLibraryModel) ?? $this->getContainer()->getMediaLibraryModel() ?? null;
    }
}
