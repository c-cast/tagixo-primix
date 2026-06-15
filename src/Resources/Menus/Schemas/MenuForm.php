<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Schemas;

use Ccast\Tagixo\Enums\MenuItemTargetType;
use Ccast\Tagixo\Models\Page;
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
                        ->itemLabel(__('Menu item'))
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
                ->required(),

            Select::make('target_page_id')
                ->label(__('Page'))
                ->options(static::pageOptions())
                ->searchable()
                ->preload()
                ->nullable()
                ->visible(fn ($get) => $get('target_type') === 'page')
                ->helperText(__('Pick the destination page.')),

            TextInput::make('target_value')
                ->label(__('Link target'))
                ->visible(fn ($get) => $get('target_type') !== 'page')
                ->helperText(__('URL, route name, or anchor (#section). Depends on the link type.'))
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
                ->itemLabel(__('Sub-item'))
                ->addActionLabel(__('Add sub-item'))
                ->schema(static::itemSchema(allowChildren: false));
        }

        return $schema;
    }

    /**
     * Options for the page picker: page id => "Title (slug)".
     *
     * Stored on the menu item as target_value (which MenuItem::resolveUrl()
     * accepts as either a numeric id or a slug).
     *
     * @return array<int, string>
     */
    protected static function pageOptions(): array
    {
        return Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->mapWithKeys(fn (Page $page) => [
                $page->id => trim(($page->title ?: __('Untitled')).' ('.$page->slug.')'),
            ])
            ->all();
    }
}
