<?php

namespace Ccast\TagixoPrimix\Resources\Popups\Pages;

use Ccast\TagixoPrimix\Resources\Popups\PopupResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditPopup extends EditRecord
{
    protected static ?string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => route('builder.popups.edit', $this->record->id)
                    .'?back='.urlencode(PopupResource::getUrl('edit', ['record' => $this->record])))

            DeleteAction::make(),
        ];
    }
}
