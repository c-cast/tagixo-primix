<?php

namespace Ccast\TagixoPrimix\Resources\Forms\Pages;

use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static ?string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(fn () => route('tagixo.forms.edit', $this->record->id)
                . '?back=' . urlencode(FormResource::getUrl('edit', ['record' => $this->record]))),

            DeleteAction::make(),
        ];
    }
}
