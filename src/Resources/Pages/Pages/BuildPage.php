<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Pages;

use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\Tagixo\Services\BuilderModelRegistryService;
use Ccast\TagixoPrimix\Concerns\CleansBuilderStructure;
use Ccast\TagixoPrimix\Pages\PrimixVisualBuilderPage;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;

class BuildPage extends PrimixVisualBuilderPage
{
    use CleansBuilderStructure;

    protected static ?string $resource = PageResource::class;

    /**
     * Get the builder context.
     */
    public function getContext(): string
    {
        return 'page';
    }

    /**
     * Authorize access to the page.
     * Override to add custom authorization.
     */
    protected function authorizeAccess(): void
    {
        // No specific authorization for now
    }

    /**
     * Return the model this page is bound to, so the Vue builder can restrict
     * the model-binding picker to the relevant model only.
     */
    public function getBoundModelForVue(): ?array
    {
        $modelClass = $this->record->model_class ?? null;
        if (! $modelClass) {
            return null;
        }

        $registry = app(BuilderModelRegistryService::class);

        foreach ($registry->listRegisteredModels() as $model) {
            if ($model['class'] === $modelClass) {
                return $registry->resolveModel($model['key']);
            }
        }

        return null;
    }

    /**
     * Load the initial structure from the Page model.
     */
    public function loadStructure(): ?string
    {
        $content = $this->record->content;

        if (is_string($content) && ! empty($content)) {
            return $content;
        }

        if (is_array($content) && ! empty($content)) {
            return json_encode($content);
        }

        return null;
    }

    /**
     * Save the structure to the Page model.
     */
    public function saveStructure(string $structure): void
    {
        $decoded = json_decode($structure, true);
        $decoded = $this->cleanStructure($decoded);

        $renderer = app(PageRenderer::class);
        $result   = $renderer->renderFromJson($decoded);

        $globalVarsCss = $renderer->generateGlobalVariablesCss();
        $componentCss  = $result['css'];
        $fullCss       = ($globalVarsCss ? $globalVarsCss . "\n" : '') . $componentCss;

        $this->record->update([
            'content'       => $decoded,
            'rendered_html' => $result['html'],
            'css'           => $fullCss ?: null,
        ]);
    }

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return __('Visual Builder') . ': ' . $this->record->title;
    }

    /**
     * Get the page heading.
     */
    public function getHeading(): string
    {
        return $this->getTitle();
    }

    /**
     * Expose page attributes to the Visual Builder Vue frontend.
     *
     * Each attribute has a translated label so the user sees the human-readable
     * name instead of the raw key. This allows modules in the Vue builder to
     * access record attributes with proper labels.
     *
     * @return array<int, array{key: string, label: string, value: mixed, type: string}>
     */
    public function getPageAttributesForVue(): array
    {
        $record          = $this->record;
        $effectiveLayout = app(\Ccast\Tagixo\Services\LayoutResolver::class)->resolve($record);

        return [
            ['key' => 'title',            'label' => __('Title'),            'value' => $record->title,            'type' => 'string'],
            ['key' => 'slug',             'label' => __('Slug'),             'value' => $record->slug,             'type' => 'string'],
            ['key' => 'excerpt',          'label' => __('Excerpt'),          'value' => $record->excerpt,          'type' => 'text'],
            ['key' => 'meta_title',       'label' => __('Meta Title'),       'value' => $record->meta_title,       'type' => 'string'],
            ['key' => 'meta_description', 'label' => __('Meta Description'), 'value' => $record->meta_description, 'type' => 'text'],
            ['key' => 'status',           'label' => __('Status'),           'value' => $record->status?->value,   'type' => 'string'],
            ['key' => 'layout_name',      'label' => __('Layout'),           'value' => $effectiveLayout?->name,   'type' => 'string'],
            ['key' => 'url',              'label' => __('URL'),              'value' => $record->url,              'type' => 'string'],
        ];
    }

    /**
     * Header/body/footer frame for the section toggler. Delegates to the core
     * LayoutFrameBuilder so the logic lives in a single place (tagixo core),
     * shared with the standalone builder and the /tagixo/builder/layout-frame
     * endpoint used by SDKs that don't inject the frame.
     */
    public function getLayoutFrameForVue(): array
    {
        return app(\Ccast\Tagixo\Builder\LayoutFrameBuilder::class)->forPage($this->record);
    }
}
