<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Pages\SiteScriptsPage;
use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\GlobalBlockResource;
use Ccast\TagixoPrimix\Resources\LayoutResource;
use Ccast\TagixoPrimix\Resources\Mails\MailResource;
use Ccast\TagixoPrimix\Resources\MediaResource;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;
use Ccast\TagixoPrimix\Resources\Popups\PopupResource;
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
        // The resource list is config-driven: comment out a line in
        // config/tagixo-primix.php to hide that builder from the admin panel.
        // Fall back to the package defaults when the config is unavailable.
        $resources = array_values(array_filter(
            (array) config('tagixo-primix.resources', $this->defaultResources()),
            fn ($resource) => is_string($resource) && class_exists($resource),
        ));

        // The fluent opt-in flags still add their resource (deduped), so
        // existing ->withMediaGallery() / ->withMailTemplates() /
        // ->withPdfTemplates() call sites keep working alongside the config.
        foreach ([
            [$this->mediaGallery, MediaResource::class],
            [$this->mailTemplates, MailResource::class],
            [$this->pdfTemplates, PdfResource::class],
        ] as [$enabled, $resource]) {
            if ($enabled && ! in_array($resource, $resources, true)) {
                $resources[] = $resource;
            }
        }

        $panel->resources($resources);

        $panel->pages([
            SiteScriptsPage::class,
        ]);
    }

    /**
     * Default resources used when config/tagixo-primix.php is not loaded.
     *
     * @return array<int, class-string>
     */
    private function defaultResources(): array
    {
        return [
            PageResource::class,
            LayoutResource::class,
            MenuResource::class,
            FormResource::class,
            SliderResource::class,
            PopupResource::class,
            GlobalBlockResource::class,
        ];
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
