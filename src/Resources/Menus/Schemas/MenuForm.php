<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Schemas;

use Ccast\TagixoPrimix\Resources\Menus\Forms\MenuTreeField;
use Ccast\TagixoPrimix\Support\SlugInput;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Components\Fields\Textarea;
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
                        ->maxLength(255)
                        ->watchBlur(SlugInput::from()),

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

                ]),

            Section::make(__('Items'))
                ->schema([
                    MenuTreeField::make('items')
                        ->label(__('Menu items'))
                        ->helperText(__('Drag to reorder; drag right to nest as a sub-item, left to outdent. Click the pencil to edit. Supports unlimited nesting.')),
                ]),
        ]);
    }
}
