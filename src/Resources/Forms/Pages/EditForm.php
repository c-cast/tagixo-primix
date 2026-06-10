<?php

namespace Ccast\TagixoPrimix\Resources\Forms\Pages;

use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static ?string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => route('builder.forms.edit', $this->record->id))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
