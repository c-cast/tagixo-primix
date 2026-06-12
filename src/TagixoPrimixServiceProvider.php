<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Console\Commands\MakeBuilderPageCommand;
use Illuminate\Support\ServiceProvider;

class TagixoPrimixServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tagixo-primix');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeBuilderPageCommand::class,
            ]);
        }
    }
}
