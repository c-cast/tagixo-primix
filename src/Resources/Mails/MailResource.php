<?php

namespace Ccast\TagixoPrimix\Resources\Mails;

use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoPrimix\Resources\Mails\Pages\BuildMail;
use Ccast\TagixoPrimix\Resources\Mails\Pages\CreateMail;
use Ccast\TagixoPrimix\Resources\Mails\Pages\EditMail;
use Ccast\TagixoPrimix\Resources\Mails\Pages\ListMails;
use Ccast\TagixoPrimix\Resources\Mails\Schemas\MailForm;
use Ccast\TagixoPrimix\Resources\Mails\Tables\MailsTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class MailResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Mail template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Mail templates');
    }

    public static function form(Form $form): Form
    {
        return MailForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return MailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMails::route('/'),
            'create' => CreateMail::route('/create'),
            'edit'   => EditMail::route('/{record}/edit'),
            'build'  => BuildMail::route('/{record}/build'),
        ];
    }
}
