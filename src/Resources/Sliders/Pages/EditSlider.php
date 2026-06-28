<?php

namespace Ccast\TagixoPrimix\Resources\Sliders\Pages;

use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\Sliders\SliderResource;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditSlider extends EditRecord
{
    protected static ?string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisualBuilderAction::make(fn () => route('builder.sliders.edit', $this->record->id)
                . '?back=' . urlencode(SliderResource::getUrl('edit', ['record' => $this->record]))),

            DeleteAction::make(),
        ];
    }
}
