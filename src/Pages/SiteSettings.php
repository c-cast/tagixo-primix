<?php

namespace Tagixo\Primix\Pages;

use Primix\Actions\Action;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Components\Fields\Toggle;
use Primix\Forms\Form;
use Primix\Forms\HasForms;
use Primix\Notifications\Notification;
use Primix\Pages\Page;
use Tagixo\PageBuilder\Models\SiteSettings as Settings;
use Tagixo\PageBuilder\Services\ErrorPageSnapshot;

/**
 * The few settings the public site reads: its name, the fallbacks for title and
 * description, the favicon and the CSS every page carries. They are the keys the
 * page builder declares (`SiteSettings::KEYS`) and nothing else — an unknown key
 * is simply not stored.
 */
class SiteSettings extends Page
{
    use HasForms;

    protected static ?string $slug = 'site-settings';

    protected static ?int $navigationSort = 90;

    /** Form state. */
    public array $data = [];

    protected ?string $title = null;

    public static function getNavigationLabel(): string
    {
        return __('Site settings');
    }

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon ?? config('tagixo-primix.icons.site-settings', 'pi pi-cog');
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup ?? config('tagixo-primix.navigation_group');
    }

    public function mount(): void
    {
        $this->title = __('Site settings');
        $this->data = Settings::settings();
        // The mode is read where the error handler reads it, not from the raw row.
        $this->data[ErrorPageSnapshot::SETTING_KEY] = app(ErrorPageSnapshot::class)->isEnabled();

        $this->form($this->getForm());
    }

    protected function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('site_name')->label(__('Site name'))->maxLength(255),
                TextInput::make('default_title')->label(__('Default title'))->maxLength(255)
                    ->helperText(__('Used by a page that does not set its own.')),
                Textarea::make('default_description')->label(__('Default description'))
                    ->helperText(__('Same, for the meta description.')),
                TextInput::make('favicon_url')->label(__('Favicon URL'))->maxLength(2048),
                Textarea::make('custom_css')->label(__('Custom CSS'))
                    ->helperText(__('Added to every page of the site.')),
                Toggle::make(ErrorPageSnapshot::SETTING_KEY)->label(__('Static error pages'))
                    ->helperText(__('Serve the error and maintenance pages built in the Theme Builder from static files, without the database. Saving an error page refreshes its file.')),
            ])
            ->statePath('data')
            ->submitAction('save')
            ->footerActions(fn () => $this->getVisibleFooterActions());
    }

    /**
     * The form has to be able to send itself: without a Save control nothing on
     * this screen could be stored (Enter in a field does not submit it).
     *
     * @return array<Action>
     */
    protected function getFooterActions(): array
    {
        return [
            Action::make('save')
                ->label(__('primix::panel.actions.save'))
                ->submit(),
        ];
    }

    public function save(): void
    {
        $this->validate(
            $this->getFormValidationRules('form'),
            $this->getFormValidationMessages('form'),
            $this->getFormValidationAttributes('form'),
        );

        $data = $this->data;
        $this->getForm('form')->dehydrateState($data);

        static::store($data);

        Notification::make()
            ->title(__('primix::panel.notifications.saved'))
            ->success()
            ->send();
    }

    /**
     * Write the settings the page builder declares, and nothing else: an empty
     * field clears the value instead of storing a blank string.
     *
     * @param  array<string, mixed>  $data
     */
    public static function store(array $data): void
    {
        // The static error pages mode is not a text setting: it also owns a marker
        // file and the snapshots, so it goes through the snapshot service and is
        // left alone when the form did not send it.
        foreach (array_diff(Settings::KEYS, [ErrorPageSnapshot::SETTING_KEY]) as $key) {
            Settings::set($key, static::text($data[$key] ?? null));
        }

        if (array_key_exists(ErrorPageSnapshot::SETTING_KEY, $data)) {
            $enabled = filter_var($data[ErrorPageSnapshot::SETTING_KEY], FILTER_VALIDATE_BOOLEAN);
            $snapshot = app(ErrorPageSnapshot::class);

            if ($enabled !== $snapshot->isEnabled()) {
                $snapshot->setEnabled($enabled);
            }
        }
    }

    protected static function text(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return ($value === '' || $value === null) ? null : (string) $value;
    }

    protected function render(): string
    {
        return 'tagixo-primix::pages.site-settings';
    }
}
