<?php

namespace Ccast\TagixoPrimix\Resources\GlobalBlocks\Pages;

use Ccast\TagixoPrimix\Resources\GlobalBlocks\GlobalBlockResource;
use Primix\Actions\Action;
use Primix\Resources\Pages\ListRecords;

class ListGlobalBlocks extends ListRecords
{
    protected static ?string $resource = GlobalBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createGlobalBlock')
                ->label(__('Create new global block'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => route('builder.global-blocks.new')
                    .'?back='.urlencode(GlobalBlockResource::getUrl('index'))),
        ];
    }
}
