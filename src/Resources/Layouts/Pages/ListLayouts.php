<?php

namespace Ccast\TagixoPrimix\Resources\Layouts\Pages;

use Ccast\TagixoPrimix\Resources\LayoutResource;
use Primix\Resources\Pages\ListRecords;
use Primix\Resources\Actions\CreateAction;

class ListLayouts extends ListRecords
{
    protected static ?string $resource = LayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
