<?php

namespace Ccast\TagixoPrimix\Resources\Mails\Pages;

use Ccast\Tagixo\Renderers\MailRenderer;
use Ccast\TagixoPrimix\Pages\PrimixVisualBuilderPage;
use Ccast\TagixoPrimix\Resources\Mails\MailResource;

class BuildMail extends PrimixVisualBuilderPage
{
    protected static ?string $resource = MailResource::class;

    public function getContext(): string
    {
        return 'mail';
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

        $extraCss = is_string($decoded['css'] ?? null) ? $decoded['css'] : '';

        $renderer = app(MailRenderer::class);
        $html     = $renderer->renderFromJson(
            $decoded,
            $extraCss,
            (string) $this->record->name,
            (string) ($this->record->preheader ?? ''),
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
            ['key' => 'name',         'label' => __('Name'),      'value' => $record->name,            'type' => 'string'],
            ['key' => 'slug',         'label' => __('Slug'),      'value' => $record->slug,            'type' => 'string'],
            ['key' => 'subject',      'label' => __('Subject'),   'value' => $record->subject,         'type' => 'string'],
            ['key' => 'preheader',    'label' => __('Preheader'), 'value' => $record->preheader,       'type' => 'text'],
            ['key' => 'status',       'label' => __('Status'),    'value' => $record->status?->value,  'type' => 'string'],
        ];
    }
}
