<?php

namespace Ccast\TagixoPrimix\Resources\GlobalBlocks;

use Ccast\Tagixo\Models\GlobalBlock;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\Pages\EditGlobalBlock;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\Pages\ListGlobalBlocks;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\Schemas\GlobalBlockForm;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\Tables\GlobalBlocksTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class GlobalBlockResource extends Resource
{
    protected static ?string $model = GlobalBlock::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Global block');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Global blocks');
    }

    public static function form(Form $form): Form
    {
        return GlobalBlockForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return GlobalBlocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Panel pages are metadata-only. The visual builder for a global block lives
     * at the plugin route `/builder/global-blocks/{id}/edit` and is opened in a
     * new tab via header/row actions (see ListGlobalBlocks + GlobalBlocksTable).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListGlobalBlocks::route('/'),
            'edit' => EditGlobalBlock::route('/{record}/edit'),
        ];
    }
}
