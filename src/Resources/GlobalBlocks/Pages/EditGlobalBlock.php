<?php

namespace Ccast\TagixoPrimix\Resources\GlobalBlocks\Pages;

use Ccast\TagixoPrimix\Resources\GlobalBlocks\GlobalBlockResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditGlobalBlock extends EditRecord
{
    protected static ?string $resource = GlobalBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => route('builder.global-blocks.edit', $this->record->id)
                    .'?back='.urlencode(GlobalBlockResource::getUrl('edit', ['record' => $this->record])))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
