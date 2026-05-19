<?php

namespace Ccast\TagixoPrimix\Resources\Layouts\Pages;

use Ccast\TagixoPrimix\Resources\LayoutResource;
use Primix\Resources\Pages\EditRecord;
use Primix\Actions\Action;

class EditLayout extends EditRecord
{
    protected static ?string $resource = LayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('build_header')
                ->label(__('Edit Header'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => LayoutResource::getUrl('build', ['record' => $this->record, 'section' => 'header'])),
            Action::make('build_footer')
                ->label(__('Edit Footer'))
                ->icon('heroicon-o-paint-brush')
                ->color('gray')
                ->url(fn () => LayoutResource::getUrl('build', ['record' => $this->record, 'section' => 'footer'])),
        ];
    }
}
