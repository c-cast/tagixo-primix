<?php

namespace Ccast\TagixoPrimix\Resources\Media\Pages;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Ccast\TagixoPrimix\Resources\MediaResource;
use Primix\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static ?string $resource = MediaResource::class;

    public function deleteCrop(int $cropId): void
    {
        $crop = Media::find($cropId);

        if ($crop && $crop->parent_id !== null) {
            $crop->delete();
        }

        $this->dispatch('$refresh');
    }
}
