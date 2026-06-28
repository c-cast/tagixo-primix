<?php

namespace Ccast\TagixoPrimix\Resources\Forms\Pages;

use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Primix\Actions\Action;
use Primix\Resources\Pages\ListRecords;

class ListForms extends ListRecords
{
    protected static ?string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createForm')
                ->label(__('Create new form'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => route('builder.forms.new')
                    . '?back=' . urlencode(FormResource::getUrl('index')))
        ];
    }
}
