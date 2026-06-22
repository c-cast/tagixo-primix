<?php

namespace Ccast\TagixoPrimix\Resources\Popups\Schemas;

use Ccast\TagixoPrimix\Support\SlugInput;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Form;

class PopupForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('Title'))
                ->required()
                ->maxLength(255)
                ->watchBlur(SlugInput::from()),

            TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->maxLength(255)
                ->suffixIcon('heroicon-o-link')
                ->placeholder(__('Slug')),

            Select::make('status')
                ->label(__('Status'))
                ->options([
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'archived' => __('Archived'),
                ])
                ->default('draft')
                ->required(),
        ]);
    }
}
