<?php

namespace Ccast\TagixoPrimix\Resources\Documents;

use Ccast\Tagixo\Models\DocumentTemplate;
use Ccast\TagixoPrimix\Resources\Documents\Pages\BuildDocument;
use Ccast\TagixoPrimix\Resources\Documents\Pages\CreateDocument;
use Ccast\TagixoPrimix\Resources\Documents\Pages\EditDocument;
use Ccast\TagixoPrimix\Resources\Documents\Pages\ListDocuments;
use Ccast\TagixoPrimix\Resources\Documents\Schemas\DocumentForm;
use Ccast\TagixoPrimix\Resources\Documents\Tables\DocumentsTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Document');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Documents');
    }

    public static function form(Form $form): Form
    {
        return DocumentForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit'   => EditDocument::route('/{record}/edit'),
            'build'  => BuildDocument::route('/{record}/build'),
        ];
    }
}
