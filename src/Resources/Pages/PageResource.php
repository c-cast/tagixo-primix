<?php

namespace Ccast\TagixoPrimix\Resources\Pages;

use Ccast\Tagixo\Models\Page;
use Ccast\TagixoPrimix\Resources\Pages\Pages\BuildPage;
use Ccast\TagixoPrimix\Resources\Pages\Pages\CreatePage;
use Ccast\TagixoPrimix\Resources\Pages\Pages\EditPage;
use Ccast\TagixoPrimix\Resources\Pages\Pages\ListPages;
use Ccast\TagixoPrimix\Resources\Pages\Schemas\PageForm;
use Ccast\TagixoPrimix\Resources\Pages\Tables\PagesTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pages');
    }

    public static function form(Form $form): Form
    {
        return PageForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit'   => EditPage::route('/{record}/edit'),
            'build'  => BuildPage::route('/{record}/build'),
        ];
    }
}
