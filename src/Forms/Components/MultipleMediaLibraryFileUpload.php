<?php

namespace Filament\SpatieLaravelMediaLibraryPlugin\Forms\Components;

use Filament\Forms2\Components\Component;
use Filament\Forms2\Components\MultipleFileUpload;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class MultipleMediaLibraryFileUpload extends MultipleFileUpload
{
    protected $collection = null;

    protected $model = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);

        $this->hydrateStateUsing(function (MultipleMediaLibraryFileUpload $component) {
            $state = $component->getUploadedFiles();

            $state[(string) Str::uuid()] = [
                'file' => null,
            ];

            return $state;
        });
    }

    public function getUploadedFiles(): array
    {
        $collection = $this->getCollection();
        $model = $this->getModel();

        if (! $collection) {
            return [];
        }

        if (! $model) {
            return [];
        }

        $files = [];

        foreach ($model->getMedia($collection) as $file) {
            $uuid = $file->uuid;

            $files[$uuid] = [
                'file' => $uuid,
            ];
        }

        return $files;
    }

    public function collection(string | callable $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function model(HasMedia | callable $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getCollection(): ?string
    {
        return $this->evaluate($this->collection);
    }

    public function getModel(): ?HasMedia
    {
        if ($model = $this->evaluate($this->model)) {
            return $model;
        }

        return $this->getContainer()->getMediaLibraryModel();
    }

    protected function getDefaultUploadComponent(): Component
    {
        return MediaLibraryFileUpload::make('file');
    }
}
