<?php

namespace Ccast\TagixoPrimix\Resources\Pdfs;

use Ccast\Tagixo\Models\PdfTemplate;
use Ccast\TagixoPrimix\Resources\Pdfs\Pages\BuildPdf;
use Ccast\TagixoPrimix\Resources\Pdfs\Pages\CreatePdf;
use Ccast\TagixoPrimix\Resources\Pdfs\Pages\EditPdf;
use Ccast\TagixoPrimix\Resources\Pdfs\Pages\ListPdfs;
use Ccast\TagixoPrimix\Resources\Pdfs\Schemas\PdfForm;
use Ccast\TagixoPrimix\Resources\Pdfs\Tables\PdfsTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class PdfResource extends Resource
{
    protected static ?string $model = PdfTemplate::class;

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
        return __('PDF template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('PDF templates');
    }

    public static function form(Form $form): Form
    {
        return PdfForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PdfsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPdfs::route('/'),
            'create' => CreatePdf::route('/create'),
            'edit'   => EditPdf::route('/{record}/edit'),
            'build'  => BuildPdf::route('/{record}/build'),
        ];
    }
}
