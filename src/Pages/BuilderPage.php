<?php

namespace Ccast\TagixoPrimix\Pages;

use Ccast\Tagixo\Concerns\InteractsWithVisualBuilder;
use Ccast\Tagixo\Contracts\BuilderPageContract;
use Ccast\TagixoPrimix\Concerns\InteractsWithVisualBuilderPrimix;
use LiVue\Attributes\Layout;
use Primix\Pages\Page;

/**
 * Abstract Builder Page (Standalone)
 *
 * Extend this class to create standalone pages with the visual builder.
 * For Resource pages, use PrimixVisualBuilderPage instead.
 *
 * Usage:
 * 1. Extend this class
 * 2. Implement getContext() to return 'page', 'form', 'mail', or 'document'
 * 3. Implement loadStructure() to load the JSON structure
 * 4. Implement saveStructure() to persist the JSON structure
 */
#[Layout('primix::components.layouts.base')]
abstract class BuilderPage extends Page implements BuilderPageContract
{
    use InteractsWithVisualBuilder;
    use InteractsWithVisualBuilderPrimix;

    protected function render(): string
    {
        return 'tagixo-primix::pages.builder-vue';
    }

    /**
     * Get the builder context
     *
     * @return string One of: 'page', 'form', 'mail', 'document'
     */
    abstract public function getContext(): string;

    /**
     * Load the initial structure
     *
     * @return string|null The JSON structure
     */
    abstract public function loadStructure(): ?string;

    /**
     * Save the structure
     *
     * @param string $structure The JSON structure to save
     */
    abstract public function saveStructure(string $structure): void;

    /**
     * Mount the page
     */
    public function mount(): void
    {
        $this->initializeVisualBuilder();
    }
}
