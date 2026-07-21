<?php

namespace Ccast\TagixoPrimix\Resources\Documents\Pages;

use Ccast\TagixoPrimix\Resources\Documents\DocumentResource;
use Primix\Resources\Actions\CreateAction;
use Primix\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static ?string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
