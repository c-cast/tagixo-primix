<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Console\Commands\MakeBuilderPageCommand;
use Ccast\TagixoPrimix\MediaGallery\Http\Livewire\MediaSelector;
use Ccast\TagixoPrimix\MediaGallery\Livewire\GlobalMediaGalleryModal;
use Illuminate\Support\ServiceProvider;
use LiVue\LiVueManager;

class TagixoPrimixServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tagixo-primix');
        $this->loadViewsFrom(__DIR__.'/../resources/views/media-gallery', 'media-gallery');

        $livue = $this->app->make(LiVueManager::class);
        $livue->register('media-gallery::media-selector', MediaSelector::class);
        $livue->register('media-gallery::global-media-gallery-modal', GlobalMediaGalleryModal::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeBuilderPageCommand::class,
            ]);
        }
    }
}
