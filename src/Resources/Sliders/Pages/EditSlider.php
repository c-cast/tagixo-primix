<?php

namespace Ccast\TagixoPrimix\Resources\Sliders\Pages;

use Ccast\TagixoPrimix\Resources\Sliders\SliderResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditSlider extends EditRecord
{
    protected static ?string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualBuilder')
                ->label(__('Open Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => route('builder.sliders.edit', $this->record->id))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
