<?php

namespace Ccast\TagixoPrimix\Resources\GlobalBlocks\Pages;

use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\GlobalBlockResource;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditGlobalBlock extends EditRecord
{
    protected static ?string $resource = GlobalBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(fn () => route('tagixo.global-blocks.edit', $this->record->id)
                .'?back='.urlencode(GlobalBlockResource::getUrl('edit', ['record' => $this->record]))),

            DeleteAction::make(),
        ];
    }
}
