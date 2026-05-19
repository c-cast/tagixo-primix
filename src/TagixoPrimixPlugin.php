<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Resources\LayoutResource;
use Ccast\TagixoPrimix\Resources\MediaResource;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Primix\Contracts\Plugin;
use Primix\Panel;

class TagixoPrimixPlugin implements Plugin
{
    private bool $mediaGallery = false;

    public function getId(): string
    {
        return 'tagixo';
    }

    public function register(Panel $panel): void
    {
        $resources = [
            PageResource::class,
            LayoutResource::class,
            MenuResource::class,
        ];

        if ($this->mediaGallery) {
            $resources[] = MediaResource::class;
        }

        $panel->resources($resources);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function withMediaGallery(bool $enabled = true): static
    {
        $this->mediaGallery = $enabled;

        return $this;
    }
}
