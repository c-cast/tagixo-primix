<?php

namespace Ccast\TagixoPrimix\Resources;

use Ccast\Tagixo\Models\Layout;
use Primix\Resources\Resource;
use Primix\Forms\Form;
use Primix\Tables\Table;
use Ccast\TagixoPrimix\Resources\Layouts\Pages\BuildLayout;

class LayoutResource extends Resource
{
    protected static ?string $model = Layout::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Layout');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Layouts');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'build' => BuildLayout::route('/{record}/build/{section?}'),
        ];
    }
}
