<?php

namespace Ccast\TagixoPrimix\MediaGallery\Http\Livewire;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Ccast\Tagixo\MediaGallery\Services\MediaService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MediaSelector extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $multiple = false;
    public ?int $maxFiles = null;
    public array $acceptedTypes = [];
    public string $search = '';
    public ?string $selectedFolder = null;
    public ?string $selectedType = null;
    public array $selected = [];
    public $files = [];
    public string $viewMode = 'grid';
    public string $activeTab = 'browse';
    public ?int $focusedMediaId = null;
    public string $selectedVariant = 'original';

    public string $editFilename = '';
    public string $editTitle = '';
    public string $editAltText = '';
    public string $editDescription = '';

    public function mount(): void
    {
        $this->multiple      = $this->multiple ?? false;
        $this->maxFiles      = $this->maxFiles ?? null;
        $this->acceptedTypes = $this->acceptedTypes ?? [];
    }

    public function getMediaQuery()
    {
        $query = Media::query()->originals()->latest();

        if (! empty($this->search)) {
            $query->search($this->search);
        }

        if ($this->selectedFolder !== null) {
            $query->inFolder($this->selectedFolder);
        }

        if ($this->selectedType) {
            match ($this->selectedType) {
                'image'    => $query->images(),
                'video'    => $query->videos(),
                'document' => $query->documents(),
                default    => null,
            };
        }

        return $query;
    }

    public function getMediaProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->getMediaQuery()->paginate(24);
    }

    public function getFoldersProperty(): Collection
    {
        return Media::query()
            ->originals()
            ->select('folder')
            ->whereNotNull('folder')
            ->distinct()
            ->pluck('folder');
    }

    public function toggleSelect(int $mediaId): void
    {
        if ($this->multiple) {
            if (in_array($mediaId, $this->selected)) {
                $this->selected = array_values(array_diff($this->selected, [$mediaId]));
            } else {
                if ($this->maxFiles && count($this->selected) >= $this->maxFiles) {
                    $this->dispatch('max-files-reached');

                    return;
                }
                $this->selected[] = $mediaId;
            }
        } else {
            $this->selected = [$mediaId];
            $this->dispatch('media-selected-from-selector', mediaIds: [$mediaId]);
        }
    }

    public function isSelected(int $mediaId): bool
    {
        return in_array($mediaId, $this->selected);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function confirmSelection(): void
    {
        $this->dispatch('media-selected-from-selector', mediaIds: $this->selected);
    }

    public function uploadFiles(): void
    {
        $this->validate([
            'files.*' => [
                'required',
                'file',
                'max:' . config('visual-builder.media_gallery.max_file_size', 10240),
            ],
        ]);

        $mediaService = app(MediaService::class);
        $uploaded     = [];

        foreach ($this->files as $file) {
            $media      = $mediaService->upload($file);
            $uploaded[] = $media->id;
        }

        $this->files     = [];
        $this->selected  = $uploaded;
        $this->activeTab = 'browse';

        if (! $this->multiple && count($uploaded) === 1) {
            $this->dispatch('media-selected-from-selector', mediaIds: $uploaded);
        }

        $this->dispatch('files-uploaded', count($uploaded));
    }

    public function resetFilters(): void
    {
        $this->search         = '';
        $this->selectedFolder = null;
        $this->selectedType   = null;
        $this->resetPage();
    }

    public function focusMedia(int $mediaId): void
    {
        $this->focusedMediaId  = $mediaId;
        $this->selectedVariant = 'original';

        $media = Media::find($mediaId);
        if ($media) {
            $this->editFilename    = $media->filename ?? '';
            $this->editTitle       = $media->title ?? '';
            $this->editAltText     = $media->alt_text ?? '';
            $this->editDescription = $media->description ?? '';
        }
    }

    public function getFocusedMediaProperty(): ?Media
    {
        if (! $this->focusedMediaId) {
            return null;
        }

        return Media::with('crops')->find($this->focusedMediaId);
    }

    public function getAvailableVariantsProperty(): array
    {
        $media = $this->focusedMedia;
        if (! $media) {
            return [];
        }

        $variants = [
            'original' => [
                'label'  => __('Original') . " ({$media->width}x{$media->height})",
                'url'    => $media->url,
                'width'  => $media->width,
                'height' => $media->height,
            ],
        ];

        if ($media->getThumbnailPath()) {
            $thumbConfig           = config('visual-builder.media_gallery.thumbnail', ['width' => 300, 'height' => 300]);
            $variants['thumbnail'] = [
                'label'  => __('Thumbnail') . " ({$thumbConfig['width']}x{$thumbConfig['height']})",
                'url'    => $media->thumbnail_url,
                'width'  => $thumbConfig['width'],
                'height' => $thumbConfig['height'],
            ];
        }

        foreach ($media->crops as $crop) {
            $key            = "crop_{$crop->id}";
            $variants[$key] = [
                'label'    => __('Crop') . " ({$crop->width}x{$crop->height})",
                'url'      => $crop->url,
                'width'    => $crop->width,
                'height'   => $crop->height,
                'media_id' => $crop->id,
            ];
        }

        return $variants;
    }

    public function saveMediaDetails(): void
    {
        if (! $this->focusedMediaId) {
            return;
        }

        $media = Media::find($this->focusedMediaId);
        if ($media) {
            $media->update([
                'filename'    => $this->editFilename,
                'title'       => $this->editTitle,
                'alt_text'    => $this->editAltText,
                'description' => $this->editDescription,
            ]);

            $this->dispatch('media-details-saved');
        }
    }

    public function getSelectedVariantUrlProperty(): ?string
    {
        $variants = $this->availableVariants;

        return $variants[$this->selectedVariant]['url'] ?? null;
    }

    public function render()
    {
        return view('media-gallery::livewire.media-selector', [
            'media'             => $this->media,
            'folders'           => $this->folders,
            'focusedMedia'      => $this->focusedMedia,
            'availableVariants' => $this->availableVariants,
        ]);
    }
}
