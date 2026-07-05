<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Console\Commands\MakeBuilderPageCommand;
use Illuminate\Support\ServiceProvider;
use LiVue\Features\SupportAssets\AssetManager as LiVueAssetManager;
use LiVue\Features\SupportAssets\Js;

class TagixoPrimixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tagixo-primix.php', 'tagixo-primix');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tagixo-primix');

        $this->app->booted(function () {
            $assetManager = $this->app->make(LiVueAssetManager::class);
            $assetManager->register([
                Js::make('tagixo-media-picker', '/vendor/tagixo/media-picker.js')->module(),
            ], 'tagixo-primix');
        });

        $this->publishes([
            __DIR__.'/../config/tagixo-primix.php' => config_path('tagixo-primix.php'),
        ], 'tagixo-primix-config');

        // Primix consumes Tagixo form schemas as app-side (resource) forms, where
        // interactive layout modules (Tabs/Wizard/Group) are first-class — so
        // enable the 'app' form target. Without an SDK like this, the Tagixo form
        // builder only offers the universal (public-site) palette.
        if (class_exists(\Ccast\Tagixo\Tagixo::class)) {
            $tagixo = app(\Ccast\Tagixo\Tagixo::class);
            $tagixo->enableAppForms();

            // App-target form previews render as a real Primix form (native
            // Tabs/Wizard) via our resource preview page.
            $tagixo->registerAppFormPreviewer(function (int|string $id): ?string {
                try {
                    return \Ccast\TagixoPrimix\Resources\Forms\FormResource::getUrl(
                        'preview-app',
                        ['record' => $id],
                    );
                } catch (\Throwable) {
                    return null;
                }
            });
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeBuilderPageCommand::class,
            ]);
        }
    }
}
