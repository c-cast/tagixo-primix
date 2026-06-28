<?php

namespace Ccast\TagixoPrimix\Resources\Popups\Pages;

use Ccast\TagixoPrimix\Resources\Popups\PopupResource;
use Primix\Actions\Action;
use Primix\Resources\Pages\ListRecords;

class ListPopups extends ListRecords
{
    protected static ?string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createPopup')
                ->label(__('Create new popup'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => route('builder.popups.new')
                    .'?back='.urlencode(PopupResource::getUrl('index'))),
        ];
    }
}
