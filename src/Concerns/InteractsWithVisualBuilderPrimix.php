<?php

namespace Ccast\TagixoPrimix\Concerns;

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

}
