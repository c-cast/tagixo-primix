<?php

namespace Ccast\TagixoPrimix\Pages;

use Ccast\Tagixo\Concerns\InteractsWithVisualBuilder;
use Ccast\Tagixo\Contracts\BuilderPageContract;
use Ccast\TagixoPrimix\Concerns\InteractsWithVisualBuilderPrimix;
use LiVue\Attributes\Layout;
use Primix\Resources\Pages\Page as ResourcePage;

/**
 * Abstract Primix Resource Page for Visual Builder
 *
 * Extend this class in your Resource's Pages directory to add visual builder.
 * For standalone pages (not tied to a Resource), use BuilderPage instead.
 *
 * Usage:
 * 1. Extend this class in your Resource's Pages directory
 * 2. Set protected static string $resource = YourResource::class
 * 3. Implement getContext() to return 'page', 'form', 'mail', or 'pdf'
 * 4. Implement loadStructure() to load JSON from your model
 * 5. Implement saveStructure() to persist JSON to your model
 * 6. Register the page in YourResource::getPages()
 */
#[Layout('primix::components.layouts.base')]
abstract class PrimixVisualBuilderPage extends ResourcePage implements BuilderPageContract
{
    use InteractsWithVisualBuilder;
    use InteractsWithVisualBuilderPrimix;

    public mixed $record;

    protected function render(): string
    {
        return 'tagixo-primix::pages.builder-vue';
    }

    /**
     * Get the builder context
     *
     * @return string One of: 'page', 'form', 'mail', 'pdf'
     */
    abstract public function getContext(): string;

    /**
     * Load the initial structure from your model
     *
     * @return string|null The JSON structure
     */
    abstract public function loadStructure(): ?string;

    /**
     * Save the structure to your model
     *
     * @param string $structure The JSON structure to save
     */
    abstract public function saveStructure(string $structure): void;

    /**
     * Mount the page with a record
     */
    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->initializeVisualBuilder();
    }

    /**
     * Resolve the record from the resource model.
     */
    public function resolveRecord(int|string $id): mixed
    {
        return static::getResource()::getModel()::findOrFail($id);
    }

    /**
     * Authorize access to the page.
     * Override this method to add custom authorization.
     */
    protected function authorizeAccess(): void
    {
        //
    }

    /**
     * Optional URL for the "Preview" button in the builder toolbar.
     *
     * When set, the Vue toolbar opens this URL directly instead of going
     * through the built-in localStorage-based preview handoff.
     */
    public function getPreviewUrl(): ?string
    {
        return null;
    }

    /**
     * URL the back/exit arrow in the builder sidebar links to.
     *
     * Defaults to the resource index page (the list view the user came
     * from). Override to point elsewhere.
     */
    public function getBackUrl(): ?string
    {
        $resource = static::getResource();

        if (! $resource) {
            return null;
        }

        return $resource::getUrl('index');
    }

    /**
     * Canvas prop types to exclude from the builder drawer.
     *
     * In the Primix panel context, some canvas prop types are irrelevant
     * because the panel already manages that functionality. For example,
     * form submission actions are handled by the consumer's PHP code
     * (Resource create/edit, custom Livewire submit), not by the visual
     * builder's SubmitPropType.
     *
     * Override this method to customize which prop types are hidden.
     *
     * @return string[]
     */
    protected function excludedCanvasPropTypes(): array
    {
        if ($this->getContext() === 'form') {
            return ['submit'];
        }

        return [];
    }

    /**
     * Override the core trait's canvas payload to filter out excluded
     * prop types before sending to the Vue frontend.
     */
    public function getCanvasForVue(): array
    {
        $canvas = app(\Ccast\Tagixo\Canvas\CanvasRegistry::class)->payloadFor(
            $this->context,
            $this->getLayoutVariant(),
        );

        $excluded = $this->excludedCanvasPropTypes();
        if (! empty($excluded)) {
            $canvas['propTypes'] = array_values(array_diff($canvas['propTypes'], $excluded));
            foreach ($excluded as $key) {
                unset($canvas['defaults'][$key]);
            }
        }

        return $canvas;
    }

    /**
     * Get page attributes for the Vue frontend.
     * Override in subclasses to expose page-level data to the canvas.
     *
     * @return array
     */
    public function getPageAttributesForVue(): array
    {
        return [];
    }
}
