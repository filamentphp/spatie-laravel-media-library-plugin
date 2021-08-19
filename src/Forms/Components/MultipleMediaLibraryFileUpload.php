<?php

namespace Filament\Forms\Components;

use Illuminate\Support\Str;

class MultipleMediaLibraryFileUpload extends MultipleFileUpload
{
    protected $collection = null;

    public function setUp(): void
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

    public function getCollection(): ?string
    {
        return $this->evaluate($this->collection) ?? $this->getName();
    }

    protected function getDefaultUploadComponent(): Component
    {
        return MediaLibraryFileUpload::make('file');
    }
}
