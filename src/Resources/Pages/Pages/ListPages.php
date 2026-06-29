<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Pages;

use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Illuminate\Support\Facades\Artisan;
use Primix\Actions\Action;
use Primix\Notifications\Notification;
use Primix\Resources\Actions\CreateAction;
use Primix\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static ?string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('generateSitemap')
                ->label(__('Generate Sitemap'))
                ->icon('heroicon-o-map')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading(__('Generate Sitemap'))
                ->modalDescription(__('This will regenerate public/sitemap.xml with all currently published pages.'))
                ->modalSubmitActionLabel(__('Generate'))
                ->action(function () {
                    try {
                        Artisan::call('tagixo:generate-sitemap');

                        Notification::make()
                            ->title(__('Sitemap generated'))
                            ->body(__('public/sitemap.xml has been updated.'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title(__('Sitemap generation failed'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
