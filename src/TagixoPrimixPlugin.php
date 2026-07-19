<?php

namespace Ccast\TagixoPrimix;

use Ccast\TagixoPrimix\Pages\ThemeBuilderPage;
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
use Ccast\Tagixo\Contracts\HasPlugin;
use Ccast\Tagixo\Tagixo;
use Ccast\TagixoPrimix\Forms\PropTypes\BooleanTablePropType;
use Ccast\TagixoPrimix\Forms\PropTypes\DateTablePropType;
use Ccast\TagixoPrimix\Forms\PropTypes\FileTablePropType;
use Ccast\TagixoPrimix\Forms\PropTypes\PrimixTablePropType;
use Primix\Contracts\Plugin;
use Primix\Panel;

class TagixoPrimixPlugin implements Plugin
{
    private bool $mediaGallery = false;

    private bool $mailTemplates = false;

    private bool $pdfTemplates = false;

    private ?string $formTarget = null;

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
        $panel->pages([ThemeBuilderPage::class]);
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
        if ($this->formTarget !== null) {
            app(Tagixo::class)->lockFormTarget($this->formTarget);
        }

        app(Tagixo::class)->extendFormModule('*',                           ['table' => PrimixTablePropType::class]);
        app(Tagixo::class)->extendFormModule(['checkbox'],                  ['table' => BooleanTablePropType::class]);
        app(Tagixo::class)->extendFormModule(['date', 'date-picker'],       ['table' => DateTablePropType::class]);
        app(Tagixo::class)->extendFormModule(['file', 'file-upload'],       ['table' => FileTablePropType::class]);
        app(Tagixo::class)->hideFormModulePropTypes('*', ['sizing']);

        foreach (app(Tagixo::class)->getPlugins() as $plugin) {
            if (! ($plugin instanceof HasPlugin)) {
                continue;
            }

            $sub = $plugin->getPlugin();

            if ($sub instanceof Plugin) {
                $sub->register($panel);
                $sub->boot($panel);
            }
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Lock all forms in this panel to a specific target ('universal' or 'app').
     * Hides the target selector — the user cannot change it.
     */
    public function formTarget(string $target): static
    {
        $this->formTarget = $target;

        return $this;
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
