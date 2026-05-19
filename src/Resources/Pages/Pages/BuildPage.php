<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Pages;

use Ccast\Tagixo\Models\Layout;
use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\TagixoPrimix\Concerns\CleansBuilderStructure;
use Ccast\TagixoPrimix\Pages\PrimixVisualBuilderPage;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;

class BuildPage extends PrimixVisualBuilderPage
{
    use CleansBuilderStructure;

    protected ?string $compiledFrontendCss = null;

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
        $effectiveLayout = $record->getEffectiveLayout();

        return [
            ['key' => 'title',            'label' => __('Title'),            'value' => $record->title,            'type' => 'string'],
            ['key' => 'slug',             'label' => __('Slug'),             'value' => $record->slug,             'type' => 'string'],
            ['key' => 'excerpt',          'label' => __('Excerpt'),          'value' => $record->excerpt,          'type' => 'text'],
            ['key' => 'meta_title',       'label' => __('Meta Title'),       'value' => $record->meta_title,       'type' => 'string'],
            ['key' => 'meta_description', 'label' => __('Meta Description'), 'value' => $record->meta_description, 'type' => 'text'],
            ['key' => 'status',           'label' => __('Status'),           'value' => $record->status?->value,   'type' => 'string'],
            ['key' => 'template',         'label' => __('Template'),         'value' => $record->template,         'type' => 'string'],
            ['key' => 'theme',            'label' => __('Theme'),            'value' => $record->theme,            'type' => 'string'],
            ['key' => 'layout_id',        'label' => __('Layout ID'),        'value' => $record->layout_id,        'type' => 'number'],
            ['key' => 'layout_name',      'label' => __('Layout'),           'value' => $effectiveLayout?->name,   'type' => 'string'],
            ['key' => 'url',              'label' => __('URL'),              'value' => $record->url,              'type' => 'string'],
        ];
    }

    public function getLayoutFrameForVue(): array
    {
        $record = $this->record;
        $assignedLayout = $record->layout;
        $globalLayout = $assignedLayout?->is_global ? $assignedLayout : Layout::global();

        return [
            'enabled' => true,
            'activeScope' => 'body',
            'body' => [
                'scope' => 'body',
                'label' => __('Body'),
                'available' => true,
                'editable' => true,
                'previewHtml' => $record->rendered_html ?? '',
                'previewCss' => $record->css ?? '',
                'structure' => $this->getStructureForVue(),
                'sourceKind' => 'page',
                'sourceDescription' => null,
            ],
            'header' => $this->buildLayoutSectionFrame($assignedLayout, $globalLayout, 'header'),
            'footer' => $this->buildLayoutSectionFrame($assignedLayout, $globalLayout, 'footer'),
        ];
    }

    protected function buildLayoutSectionFrame(?Layout $assignedLayout, ?Layout $globalLayout, string $section): array
    {
        [$sourceLayout, $sourceKind] = $this->resolveLayoutSectionSource($assignedLayout, $globalLayout, $section);
        $editableLayout = $sourceLayout ?? $globalLayout ?? $assignedLayout;
        $editableKind = $sourceLayout ? $sourceKind : ($editableLayout?->is_global ? 'global' : ($editableLayout ? 'assigned' : 'none'));
        $contentField = "{$section}_content";
        $cssField = "{$section}_css";
        $htmlField = "{$section}_rendered_html";
        $structure = $sourceLayout ? $this->normalizeBuilderStructure($sourceLayout->getAttribute($contentField)) : ['body' => [], 'components' => []];

        return [
            'scope' => $section,
            'label' => __($section === 'header' ? 'Header' : 'Footer'),
            'available' => $sourceLayout !== null && (($sourceLayout->getAttribute($htmlField) ?? '') !== '' || $structure['components'] !== []),
            'editable' => $editableLayout !== null,
            'previewHtml' => $sourceLayout?->getAttribute($htmlField) ?? '',
            'previewCss' => $this->composeLayoutPreviewCss($sourceLayout?->getAttribute($cssField)),
            'structure' => $structure,
            'sourceKind' => $editableKind,
            'sourceLayoutId' => $editableLayout?->id,
            'sourceLayoutName' => $editableLayout?->name,
            'sourceDescription' => $this->layoutSectionDescription($section, $editableLayout, $editableKind),
            'isFallback' => $sourceLayout !== null && $assignedLayout !== null && $sourceLayout->id !== $assignedLayout->id,
            'saveUrl' => $editableLayout ? route('tagixo.layouts.sections.save', ['id' => $editableLayout->id, 'section' => $section]) : null,
        ];
    }

    protected function resolveLayoutSectionSource(?Layout $assignedLayout, ?Layout $globalLayout, string $section): array
    {
        if ($this->layoutSectionConfigured($assignedLayout, $section)) {
            return [$assignedLayout, 'assigned'];
        }

        if ($this->layoutSectionConfigured($globalLayout, $section)) {
            return [$globalLayout, 'global'];
        }

        return [null, 'none'];
    }

    protected function layoutSectionConfigured(?Layout $layout, string $section): bool
    {
        if (! $layout) {
            return false;
        }

        $content = $layout->getAttribute("{$section}_content");

        if (is_array($content)) {
            if (isset($content['components']) && is_array($content['components'])) {
                return $content['components'] !== [] || ((isset($content['body']) && is_array($content['body'])) ? $content['body'] !== [] : false);
            }

            return $content !== [];
        }

        if (is_string($content)) {
            return trim($content) !== '';
        }

        return ($layout->getAttribute("{$section}_rendered_html") ?? null) !== null;
    }

    protected function normalizeBuilderStructure(mixed $structure): array
    {
        if (is_array($structure)) {
            if (array_key_exists('components', $structure)) {
                return [
                    'body' => is_array($structure['body'] ?? null) ? $structure['body'] : [],
                    'components' => is_array($structure['components'] ?? null) ? $structure['components'] : [],
                ];
            }

            return [
                'body' => [],
                'components' => $structure,
            ];
        }

        if (is_string($structure) && trim($structure) !== '') {
            $decoded = json_decode($structure, true);
            if (is_array($decoded)) {
                return $this->normalizeBuilderStructure($decoded);
            }
        }

        return ['body' => [], 'components' => []];
    }

    protected function layoutSectionDescription(string $section, ?Layout $layout, string $sourceKind): string
    {
        $label = $section === 'header' ? __('header') : __('footer');

        if (! $layout) {
            return __('No layout is available to store this :section yet.', ['section' => $label]);
        }

        if ($sourceKind === 'global') {
            return __('Changes to this :section update the global default layout.', ['section' => $label]);
        }

        return __('Changes to this :section update the assigned layout only.', ['section' => $label]);
    }

    protected function composeLayoutPreviewCss(?string $sectionCss): string
    {
        $parts = array_filter([
            trim($this->getCompiledFrontendCss()),
            trim((string) $sectionCss),
        ]);

        return implode("\n", $parts);
    }

    protected function getCompiledFrontendCss(): string
    {
        if ($this->compiledFrontendCss !== null) {
            return $this->compiledFrontendCss;
        }

        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            return $this->compiledFrontendCss = '';
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $entry = $manifest['vendor/ccast/tagixo/resources/css/frontend.css']['file'] ?? null;

        if (! is_string($entry) || $entry === '') {
            return $this->compiledFrontendCss = '';
        }

        $assetPath = public_path('build/' . ltrim($entry, '/'));

        if (! is_file($assetPath)) {
            return $this->compiledFrontendCss = '';
        }

        return $this->compiledFrontendCss = (string) file_get_contents($assetPath);
    }
}
