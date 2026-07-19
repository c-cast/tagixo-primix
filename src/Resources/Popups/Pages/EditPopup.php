<?php

namespace Ccast\TagixoPrimix\Resources\Popups\Pages;

use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\Popups\PopupResource;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditPopup extends EditRecord
{
    protected static ?string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(fn () => route('tagixo.popups.edit', $this->record->id)
                .'?back='.urlencode(PopupResource::getUrl('edit', ['record' => $this->record]))),

            DeleteAction::make(),
        ];
    }
}
