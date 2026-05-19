<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Schemas;

use Ccast\Tagixo\Enums\MenuItemTargetType;
use Primix\Forms\Components\Fields\Repeater;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\Toggle;
use Primix\Forms\Components\Layouts\Section;
use Primix\Forms\Form;

class MenuForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Section::make(__('Menu details'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('slug')
                        ->label(__('Slug'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. main-nav'))
                        ->helperText(__('Unique identifier used to reference this menu from modules.')),

                    Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->nullable(),

                    TextInput::make('css_class')
                        ->label(__('Wrapper CSS class'))
                        ->nullable()
                        ->placeholder(__('e.g. navbar-primary'))
                        ->helperText(__('Applied to the <nav> wrapper when the menu is rendered.')),
                ]),

            Section::make(__('Items'))
                ->schema([
                    Repeater::make('items')
                        ->label(__('Menu items'))
                        ->reorderable(true)
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                        ->addActionLabel(__('Add item'))
                        ->schema(static::itemSchema(allowChildren: true)),
                ]),
        ]);
    }

    protected static function itemSchema(bool $allowChildren = false): array
    {
        $schema = [
            TextInput::make('label')
                ->label(__('Label'))
                ->required()
                ->maxLength(255),

            Select::make('target_type')
                ->label(__('Link type'))
                ->options(MenuItemTargetType::options())
                ->default('url')
                ->required()
                ->live(),

            TextInput::make('target_value')
                ->label(__('Link target'))
                ->helperText(__('URL, page slug or id, route name, or anchor (#section). Depends on link type.'))
                ->nullable(),

            Toggle::make('new_tab')
                ->label(__('Open in new tab'))
                ->default(false),

            TextInput::make('icon')
                ->label(__('Icon'))
                ->placeholder('heroicon-o-home')
                ->nullable()
                ->helperText(__('Optional Heroicon identifier.')),

            TextInput::make('css_class')
                ->label(__('Item CSS class'))
                ->nullable(),

            Toggle::make('visible')
                ->label(__('Visible'))
                ->default(true),
        ];

        if ($allowChildren) {
            $schema[] = Repeater::make('children')
                ->label(__('Sub-items'))
                ->reorderable(true)
                ->collapsible()
                ->cloneable()
                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                ->addActionLabel(__('Add sub-item'))
                ->schema(static::itemSchema(allowChildren: false));
        }

        return $schema;
    }
}
