<?php

namespace Ccast\TagixoPrimix\Resources;

use Ccast\Tagixo\Models\Layout;
use Primix\Resources\Resource;
use Primix\Forms\Form;
use Primix\Tables\Table;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Columns\IconColumn;
use Primix\Actions\Action;
use Primix\Resources\Actions\EditAction;
use Primix\Resources\Actions\DeleteAction;
use Ccast\TagixoPrimix\Resources\Layouts\Pages\ListLayouts;
use Ccast\TagixoPrimix\Resources\Layouts\Pages\CreateLayout;
use Ccast\TagixoPrimix\Resources\Layouts\Pages\EditLayout;
use Ccast\TagixoPrimix\Resources\Layouts\Pages\BuildLayout;

class LayoutResource extends Resource
{
    protected static ?string $model = Layout::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): string
    {
        return __('Visual Builder');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('Layouts');
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
        return $form->schema([
            TextInput::make('name')
                ->label(__('Name'))
                ->required()
                ->maxLength(255),
            // The global layout is system-managed (seeded). It is not switchable
            // from the panel, so the `is_global` toggle is intentionally omitted;
            // the table still surfaces which layout is global (read-only).
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_global')
                    ->label(__('Global'))
                    ->boolean(),
                TextColumn::make('pages_count')
                    ->label(__('Pages'))
                    ->getStateUsing(fn (Layout $record): int => $record->pages()->count()),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('build_header')
                    ->label(__('Header'))
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (Layout $record) => static::getUrl('build', ['record' => $record, 'section' => 'header'])),
                Action::make('build_footer')
                    ->label(__('Footer'))
                    ->icon('heroicon-o-paint-brush')
                    ->color('gray')
                    ->url(fn (Layout $record) => static::getUrl('build', ['record' => $record, 'section' => 'footer'])),
                EditAction::make(),
                // The global layout is the system base and cannot be deleted
                // (enforced in Layout::booted()); hide the action for it too.
                DeleteAction::make()
                    ->hidden(fn (Layout $record): bool => $record->is_global),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLayouts::route('/'),
            'create' => CreateLayout::route('/create'),
            'edit'   => EditLayout::route('/{record}/edit'),
            'build'  => BuildLayout::route('/{record}/build/{section?}'),
        ];
    }
}
