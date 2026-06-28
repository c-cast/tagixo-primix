<?php

namespace Ccast\TagixoPrimix\Resources\Sliders\Pages;

use Ccast\TagixoPrimix\Resources\Sliders\SliderResource;
use Primix\Actions\Action;
use Primix\Resources\Pages\ListRecords;

class ListSliders extends ListRecords
{
    protected static ?string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createSlider')
                ->label(__('Create new slider'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => route('builder.sliders.new')
                    . '?back=' . urlencode(SliderResource::getUrl('index'))),
        ];
    }
}
