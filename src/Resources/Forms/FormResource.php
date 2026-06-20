<?php

namespace Ccast\TagixoPrimix\Resources\Forms;

use Ccast\Tagixo\Models\FormSchema;
use Ccast\TagixoPrimix\Resources\Forms\Pages\EditForm;
use Ccast\TagixoPrimix\Resources\Forms\Pages\ListForms;
use Ccast\TagixoPrimix\Resources\Forms\Schemas\FormForm;
use Ccast\TagixoPrimix\Resources\Forms\Tables\FormsTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class FormResource extends Resource
{
    protected static ?string $model = FormSchema::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Form');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Forms');
    }

    public static function form(Form $form): Form
    {
        return FormForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return FormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Panel pages are metadata-only. The visual form builder lives at the
     * plugin route `/builder/forms/{id}/edit` and is opened in a new tab
     * via header/row actions (see ListForms + FormsTable).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'edit'  => EditForm::route('/{record}/edit'),
            // Standalone preview of an app-target form, rendered as a real Primix
            // form (native Tabs/Wizard). Tagixo core delegates here for app forms.
            'preview-app' => \Ccast\TagixoPrimix\Resources\Forms\Pages\PreviewAppForm::route('/{record}/preview-app'),
        ];
    }
}
