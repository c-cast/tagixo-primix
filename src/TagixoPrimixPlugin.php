<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Resources\LayoutResource;
use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Ccast\TagixoPrimix\Resources\Mails\MailResource;
use Ccast\TagixoPrimix\Resources\MediaResource;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;
use Ccast\TagixoPrimix\Resources\Sliders\SliderResource;
use Primix\Contracts\Plugin;
use Primix\Panel;

class TagixoPrimixPlugin implements Plugin
{
    private bool $mediaGallery = false;

    private bool $mailTemplates = false;

    private bool $pdfTemplates = false;

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
            FormResource::class,
            SliderResource::class,
        ];

        if ($this->mediaGallery) {
            $resources[] = MediaResource::class;
        }

        if ($this->mailTemplates) {
            $resources[] = MailResource::class;
        }

        if ($this->pdfTemplates) {
            $resources[] = PdfResource::class;
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

    public function withMailTemplates(bool $enabled = true): static
    {
        $this->mailTemplates = $enabled;

        return $this;
    }

    public function withPdfTemplates(bool $enabled = true): static
    {
        $this->pdfTemplates = $enabled;

        return $this;
    }
}
