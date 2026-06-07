<?php

namespace Ccast\TagixoPrimix\Tests\Support;

use Ccast\TagixoPrimix\TagixoPrimixPlugin;
use Primix\Panel;
use Primix\PanelProvider;

/**
 * Test panel that mirrors the consumer's admin setup: registers Tagixo's
 * Primix plugin with mail + pdf templates enabled. Naming `AdminPanelProvider`
 * gives the panel id `admin`, so the routes resolve to `primix.admin.*` as
 * referenced by the migrated tests.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->path('admin')
            ->login()
            ->plugin(
                TagixoPrimixPlugin::make()
                    ->withMailTemplates()
                    ->withPdfTemplates()
            );
    }
}
