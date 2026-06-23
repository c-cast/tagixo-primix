<?php

namespace Ccast\TagixoPrimix\Resources\GlobalBlocks\Schemas;

use Ccast\TagixoPrimix\Support\SlugInput;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Form;

class GlobalBlockForm
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

            TextInput::make('category')
                ->label(__('Category'))
                ->maxLength(255)
                ->placeholder(__('E.g. Headers, CTA, Footers...')),

            Select::make('status')
                ->label(__('Status'))
                ->options([
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'archived' => __('Archived'),
                ])
                ->default('published')
                ->required(),
        ]);
    }
}
