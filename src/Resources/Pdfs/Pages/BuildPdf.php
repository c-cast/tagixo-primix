<?php

namespace Ccast\TagixoPrimix\Resources\Pdfs\Pages;

use Ccast\Tagixo\Renderers\PdfRenderer;
use Ccast\TagixoPrimix\Concerns\CleansBuilderStructure;
use Ccast\TagixoPrimix\Pages\PrimixVisualBuilderPage;
use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;

class BuildPdf extends PrimixVisualBuilderPage
{
    use CleansBuilderStructure;

    protected static ?string $resource = PdfResource::class;

    public function getContext(): string
    {
        return 'pdf';
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
        $decoded = $this->cleanStructure($decoded);

        $extraCss = is_string($decoded['css'] ?? null) ? $decoded['css'] : '';

        $renderer = app(PdfRenderer::class);
        $html     = $renderer->renderFromJson(
            $decoded,
            $extraCss,
            (string) $this->record->name,
            (string) ($this->record->paper_size ?? 'A4'),
            (string) ($this->record->orientation ?? 'portrait'),
            (string) ($this->record->margin ?? '2cm'),
        );

        $this->record->update([
            'content'       => $decoded,
            'rendered_html' => $html,
            'css'           => $extraCss !== '' ? $extraCss : null,
        ]);
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

        return [
            ['key' => 'name',         'label' => __('Name'),         'value' => $record->name,            'type' => 'string'],
            ['key' => 'slug',         'label' => __('Slug'),         'value' => $record->slug,            'type' => 'string'],
            ['key' => 'paper_size',   'label' => __('Paper size'),   'value' => $record->paper_size,      'type' => 'string'],
            ['key' => 'orientation',  'label' => __('Orientation'),  'value' => $record->orientation,     'type' => 'string'],
            ['key' => 'margin',       'label' => __('Margin'),       'value' => $record->margin,          'type' => 'string'],
            ['key' => 'status',       'label' => __('Status'),       'value' => $record->status?->value,  'type' => 'string'],
        ];
    }
}
