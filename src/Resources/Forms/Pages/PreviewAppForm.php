<?php

namespace Ccast\TagixoPrimix\Resources\Forms\Pages;

use Ccast\Tagixo\Models\FormSchema;
use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Ccast\TagixoPrimix\Support\TagixoFormToPrimix;
use Primix\Forms\Form;
use Primix\Forms\HasForms;
use Primix\Resources\Pages\Page;

/**
 * Standalone preview of an 'app'-target Tagixo form, rendered as a REAL Primix
 * form (native interactive Tabs/Wizard) — this is the SDK previewer the Tagixo
 * core delegates to (Tagixo::registerAppFormPreviewer). The saved Tagixo schema
 * is mapped to Primix Forms definitions and built via Form::fromSchema().
 */
class PreviewAppForm extends Page
{
    use HasForms;

    protected static ?string $resource = FormResource::class;

    public ?FormSchema $record = null;

    /** Form state path. */
    public array $data = [];

    /** Mapped Primix form definitions (persisted across LiVue requests). */
    public array $previewDefinitions = [];

    protected ?string $title = null;

    public function mount(int|string $record): void
    {
        $this->record = FormSchema::findOrFail($record);
        $this->title = trim((string) ($this->record->title ?? '')) . ' — ' . __('Preview');

        $content = is_array($this->record->content ?? null) ? $this->record->content : [];
        $components = is_array($content['components'] ?? null)
            ? $content['components']
            : (is_array($this->record->fields ?? null) ? $this->record->fields : []);

        $body = is_array($content['body'] ?? null) ? $content['body'] : [];
        $rootColumns = (int) ($body['grid']['columns']['value'] ?? $body['grid']['columns'] ?? 12);

        $this->previewDefinitions = app(TagixoFormToPrimix::class)->toDefinitions($components, $rootColumns);
    }

    /**
     * Build the preview form from the mapped definitions. No submit action — the
     * preview is for inspecting layout/behaviour, not submitting.
     */
    public function form(Form $form): Form
    {
        return $form
            ->fromSchema($this->previewDefinitions)
            ->statePath('data');
    }

    protected function render(): string
    {
        return 'tagixo-primix::pages.form-preview-app';
    }
}
