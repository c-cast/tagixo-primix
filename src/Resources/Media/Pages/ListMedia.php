<?php

namespace Ccast\TagixoPrimix\Resources\Media\Pages;

use Ccast\TagixoPrimix\Resources\MediaResource;
use Primix\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static ?string $resource = MediaResource::class;
}
