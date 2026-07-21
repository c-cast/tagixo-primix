<?php

namespace Ccast\TagixoPrimix\Resources\Documents\Pages;

use Ccast\Tagixo\Renderers\PageRenderer;
use Ccast\Tagixo\Renderers\DocumentRenderer;
use Ccast\TagixoPrimix\Pages\PrimixVisualBuilderPage;
use Ccast\TagixoPrimix\Resources\Documents\DocumentResource;

class BuildDocument extends PrimixVisualBuilderPage
{
    protected static ?string $resource = DocumentResource::class;

    public function getContext(): string
    {
        return 'document';
    }

    protected function authorizeAccess(): void
    {
        //
    }

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

    public function saveStructure(string $structure): void
    {
        $decoded = json_decode($structure, true);

        // Extract header/footer zones (sent alongside body)
        $headerComponents = $decoded['header_components'] ?? null;
        $footerComponents = $decoded['footer_components'] ?? null;

        // Body-only content for rendering
        $bodyContent = [
            'components' => $decoded['components'] ?? [],
            'body'       => $decoded['body'] ?? [],
        ];

        $extraCss = is_string($decoded['css'] ?? null) ? $decoded['css'] : '';

        // Resolve header/footer content: prefer what was just sent, fall back to what's stored
        $headerContent = $headerComponents !== null
            ? ['components' => $headerComponents, 'body' => []]
            : (is_array($this->record->header_content) ? $this->record->header_content : []);
        $footerContent = $footerComponents !== null
            ? ['components' => $footerComponents, 'body' => []]
            : (is_array($this->record->footer_content) ? $this->record->footer_content : []);

        $renderer = app(DocumentRenderer::class);
        $html     = $renderer->renderFromJson(
            $bodyContent,
            $extraCss,
            (string) $this->record->name,
            (string) ($this->record->paper_size ?? 'A4'),
            (string) ($this->record->orientation ?? 'portrait'),
            (string) ($this->record->margin ?? '2cm'),
            $headerContent,
            $footerContent,
            (int) ($this->record->header_height ?? 20),
            (int) ($this->record->footer_height ?? 15),
        );

        $updateData = [
            'content'       => $bodyContent,
            'rendered_html' => $html,
            'css'           => $extraCss !== '' ? $extraCss : null,
        ];

        if ($headerComponents !== null) {
            $updateData['header_content'] = json_encode($headerContent);
        }
        if ($footerComponents !== null) {
            $updateData['footer_content'] = json_encode($footerContent);
        }

        // Use a raw DB update for header/footer JSON fields: Eloquent's dirty
        // detection fails for uncasted JSONB columns (it sees string→string and
        // may skip the UPDATE even when the content changed).
        $jsonFields = array_intersect_key($updateData, array_flip(['header_content', 'footer_content']));
        $scalarFields = array_diff_key($updateData, $jsonFields);

        if (! empty($jsonFields)) {
            \DB::table($this->record->getTable())
                ->where($this->record->getKeyName(), $this->record->getKey())
                ->update($jsonFields);
            $this->record->syncOriginal();
        }

        if (! empty($scalarFields)) {
            $this->record->update($scalarFields);
        }
    }

    public function getTitle(): string
    {
        return __('Visual Builder') . ': ' . $this->record->name;
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function getPageAttributesForVue(): array
    {
        $record = $this->record;

        // header_content / footer_content are stored as JSONB and read back as
        // a raw JSON string by PDO (no array cast on the model). Use it directly;
        // do NOT json_encode an already-encoded string or the JS receives a
        // double-encoded value and parseZone() falls back to [].
        $headerStructure = is_string($record->header_content) && $record->header_content !== ''
            ? $record->header_content
            : (is_array($record->header_content) ? json_encode($record->header_content) : '{"components":[],"body":[]}');
        $footerStructure = is_string($record->footer_content) && $record->footer_content !== ''
            ? $record->footer_content
            : (is_array($record->footer_content) ? json_encode($record->footer_content) : '{"components":[],"body":[]}');

        // Pre-render header/footer for the canvas combined-view preview
        $pageRenderer      = app(PageRenderer::class);
        $headerPreview     = '';
        $footerPreview     = '';

        $headerContentArr = is_string($record->header_content) ? json_decode($record->header_content, true) : $record->header_content;
        $footerContentArr = is_string($record->footer_content) ? json_decode($record->footer_content, true) : $record->footer_content;

        if (! empty($headerContentArr['components'])) {
            $r             = $pageRenderer->setContext('document')->renderFromJson($headerContentArr);
            $css           = trim($r['css'] ?? '');
            $headerPreview = ($css ? "<style>{$css}</style>" : '') . ($r['html'] ?? '');
        }

        if (! empty($footerContentArr['components'])) {
            $r             = $pageRenderer->setContext('document')->renderFromJson($footerContentArr);
            $css           = trim($r['css'] ?? '');
            $footerPreview = ($css ? "<style>{$css}</style>" : '') . ($r['html'] ?? '');
        }

        return [
            ['key' => 'name',              'label' => __('Name'),          'value' => $record->name,                        'type' => 'string'],
            ['key' => 'slug',              'label' => __('Slug'),          'value' => $record->slug,                        'type' => 'string'],
            ['key' => 'paper_size',        'label' => __('Paper size'),    'value' => $record->paper_size,                  'type' => 'string'],
            ['key' => 'orientation',       'label' => __('Orientation'),   'value' => $record->orientation,                 'type' => 'string'],
            ['key' => 'margin',            'label' => __('Margin'),        'value' => $record->margin,                      'type' => 'string'],
            ['key' => 'header_height',     'label' => __('Header height'), 'value' => (int) ($record->header_height ?? 20), 'type' => 'number'],
            ['key' => 'footer_height',     'label' => __('Footer height'), 'value' => (int) ($record->footer_height ?? 15), 'type' => 'number'],
            ['key' => 'header_structure',  'label' => __('Header'),        'value' => $headerStructure,                     'type' => 'string'],
            ['key' => 'footer_structure',  'label' => __('Footer'),        'value' => $footerStructure,                     'type' => 'string'],
            ['key' => 'header_preview',    'label' => __('Header preview'),'value' => $headerPreview,                       'type' => 'string'],
            ['key' => 'footer_preview',    'label' => __('Footer preview'),'value' => $footerPreview,                       'type' => 'string'],
            ['key' => 'status',            'label' => __('Status'),        'value' => $record->status?->value,              'type' => 'string'],
            ['key' => 'page_templates',    'label' => __('Page templates'),'value' => $record->page_templates !== null ? json_encode($record->page_templates ?? []) : null, 'type' => 'json'],
        ];
    }
}
