<?php

namespace Ccast\TagixoPrimix\Resources;

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoPrimix\Resources\Menus\Pages\CreateMenu;
use Ccast\TagixoPrimix\Resources\Menus\Pages\EditMenu;
use Ccast\TagixoPrimix\Resources\Menus\Pages\ListMenus;
use Ccast\TagixoPrimix\Resources\Menus\Schemas\MenuForm;
use Ccast\TagixoPrimix\Resources\Menus\Tables\MenusTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('Menus');
    }

    public static function getModelLabel(): string
    {
        return __('Menu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Menus');
    }

    public static function form(Form $form): Form
    {
        return MenuForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'edit'   => EditMenu::route('/{record}/edit'),
        ];
    }
}
