<?php

namespace Ccast\TagixoPrimix\Resources\Popups;

use Ccast\Tagixo\Models\Popup;
use Ccast\TagixoPrimix\Resources\Popups\Pages\EditPopup;
use Ccast\TagixoPrimix\Resources\Popups\Pages\ListPopups;
use Ccast\TagixoPrimix\Resources\Popups\Schemas\PopupForm;
use Ccast\TagixoPrimix\Resources\Popups\Tables\PopupsTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Popup');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Popups');
    }

    public static function form(Form $form): Form
    {
        return PopupForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PopupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Panel pages are metadata-only. The visual popup builder lives at the
     * plugin route `/builder/popups/{id}/edit` and is opened in a new tab
     * via header/row actions (see ListPopups + PopupsTable).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListPopups::route('/'),
            'edit' => EditPopup::route('/{record}/edit'),
        ];
    }
}
