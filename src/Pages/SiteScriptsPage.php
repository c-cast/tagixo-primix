<?php

namespace Ccast\TagixoPrimix\Pages;

use Ccast\Tagixo\Models\SiteScript;
use Primix\Actions\Action;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\Toggle;
use Primix\Forms\Form;
use Primix\Forms\HasForms;
use Primix\Notifications\Notification;
use Primix\Pages\Page;

/**
 * Admin settings page to manage site-wide custom scripts.
 *
 * Edits three raw markup blobs — one per injection location (head, body-open,
 * body-close) — persisted to {@see SiteScript} and printed verbatim into every
 * public page by the Tagixo frontend layout.
 */
class SiteScriptsPage extends Page
{
    use HasForms;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationLabel = 'Site Scripts';

    protected static ?string $navigationGroup = 'Visual Builder';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'site-scripts';

    protected ?string $title = null;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->title = __('Site Scripts');

        foreach (SiteScript::LOCATIONS as $location) {
            $value = SiteScript::valueFor($location);
            $this->data[$location] = $value['content'];
            $this->data[$location.'_enabled'] = $value['enabled'];
        }

        $this->form($this->getForm());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...$this->locationFields('head'),
                ...$this->locationFields('body_open'),
                ...$this->locationFields('body_close'),
            ])
            ->statePath('data')
            ->submitAction('save')
            ->submitButton(
                Action::make('save')
                    ->label(__('Save'))
                    ->submit()
            );
    }

    /**
     * Build the enable toggle + textarea pair for one injection location.
     *
     * @return array<int, \Primix\Forms\Components\Fields\Field>
     */
    protected function locationFields(string $location): array
    {
        $labels = [
            'head' => __('Head — before </head> (analytics, tag manager, verification meta)'),
            'body_open' => __('Body start — right after <body> (e.g. GTM noscript)'),
            'body_close' => __('Body end — before </body> (deferred widgets, chat)'),
        ];

        return [
            Toggle::make($location.'_enabled')
                ->label($labels[$location] ?? $location)
                ->columnSpanFull(),
            Textarea::make($location)
                ->hiddenLabel()
                ->rows(8)
                ->columnSpanFull(),
        ];
    }

    public function save(): void
    {
        foreach (SiteScript::LOCATIONS as $location) {
            SiteScript::setFor(
                $location,
                $this->data[$location] ?? null,
                (bool) ($this->data[$location.'_enabled'] ?? true),
            );
        }

        Notification::make()
            ->title(__('Site scripts saved'))
            ->success()
            ->send();
    }

    protected function render(): string
    {
        return 'tagixo-primix::pages.site-scripts';
    }
}
