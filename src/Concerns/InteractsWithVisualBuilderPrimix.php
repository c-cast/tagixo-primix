<?php

namespace Ccast\TagixoPrimix\Concerns;

use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\Tagixo\Services\BuilderApiService;
use Ccast\Tagixo\Core\StyleGenerator;
use LiVue\Component as LiVueComponent;
use LiVue\Features\SupportHooks\HookRegistry;
use Primix\Notifications\Notification;

/**
 * Primix/LiVue bridge for InteractsWithVisualBuilder
 *
 * Implements the abstract methods from the core trait using Primix Notifications
 * and LiVue dispatch/skipRender.
 *
 * Pages using the visual builder should use both traits:
 *   use InteractsWithVisualBuilder;          // core logic
 *   use InteractsWithVisualBuilderPrimix;    // Primix/LiVue bridge
 */
trait InteractsWithVisualBuilderPrimix
{
    public function getInitialStylesheet(): string
    {
        if (class_exists(BuilderApiService::class)) {
            return app(BuilderApiService::class)->stylesheet($this->getStructureForVue(), $this->context);
        }

        $renderer = app(PageRenderer::class);
        $globalVarsCss = $renderer->generateGlobalVariablesCss();
        $componentCss = StyleGenerator::generateAllStyles($this->components);

        return trim(($globalVarsCss ? $globalVarsCss . "\n" : '') . $componentCss);
    }

    protected function notifySuccess(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->success()
            ->title($title);

        if ($body !== null) {
            $notification->body($body);
        }

        $notification->send();
    }

    protected function notifyError(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->danger()
            ->title($title);

        if ($body !== null) {
            $notification->body($body);
        }

        $notification->send();
    }

    protected function dispatchBuilderEvent(string $event, mixed ...$params): void
    {
        $this->dispatch($event, ...$params);
    }

    protected function skipBuilderRender(): void
    {
        if (method_exists($this, 'skipRender')) {
            $this->skipRender();

            return;
        }

        if ($this instanceof LiVueComponent) {
            app(HookRegistry::class)->store($this)->set('renderless', true);
        }
    }

    /**
     * Regenerate stylesheet for live canvas preview.
     * Called from the Vue frontend via $wire.regenerateStylesheet(structure).
     */
    public function regenerateStylesheet(string|array $structure): string
    {
        $parsed = is_array($structure) ? $structure : json_decode($structure, true);

        if (! is_array($parsed)) {
            return '';
        }

        // Prefer canonical core service when available.
        if (class_exists(BuilderApiService::class)) {
            return app(BuilderApiService::class)->stylesheet($parsed, $this->context);
        }

        // Fallback for older core versions.
        $components = $parsed['components'] ?? [];
        $renderer = app(PageRenderer::class);
        $globalVarsCss = $renderer->generateGlobalVariablesCss();
        $componentCss = StyleGenerator::generateAllStyles($components);

        return trim(($globalVarsCss ? $globalVarsCss . "\n" : '') . $componentCss);
    }
}
