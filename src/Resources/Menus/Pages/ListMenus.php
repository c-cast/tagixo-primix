<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Pages;

use Ccast\TagixoPrimix\Resources\MenuResource;
use Primix\Resources\Actions\CreateAction;
use Primix\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    protected static ?string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
