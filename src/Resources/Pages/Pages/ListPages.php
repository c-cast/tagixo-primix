<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Pages;

use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Primix\Resources\Actions\CreateAction;
use Primix\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static ?string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
