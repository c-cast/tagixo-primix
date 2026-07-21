<?php

namespace Ccast\TagixoPrimix\Resources\Documents\Schemas;

use Ccast\TagixoPrimix\Support\SlugInput;
use Primix\Forms\Components\Fields\DatePicker;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Form;

class DocumentForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('Name'))
                ->required()
                ->maxLength(255)
                ->watchBlur(SlugInput::from()),

            TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->maxLength(255)
                ->suffixIcon('heroicon-o-link')
                ->placeholder(__('Slug')),

            Select::make('paper_size')
                ->label(__('Paper size'))
                ->options([
                    'A4' => 'A4',
                    'A3' => 'A3',
                    'A5' => 'A5',
                    'letter' => __('Letter'),
                    'legal' => __('Legal'),
                ])
                ->default('A4')
                ->required(),

            Select::make('orientation')
                ->label(__('Orientation'))
                ->options([
                    'portrait' => __('Portrait'),
                    'landscape' => __('Landscape'),
                ])
                ->default('portrait')
                ->required(),

            TextInput::make('margin')
                ->label(__('Margin'))
                ->maxLength(32)
                ->default('2cm')
                ->helperText(__('CSS-style margin applied to @page (e.g. 2cm, 15mm, 1in).')),

            Select::make('status')
                ->label(__('Status'))
                ->options([
                    'draft' => __('Draft'),
                    'published' => __('Published'),
                    'scheduled' => __('Scheduled'),
                    'archived' => __('Archived'),
                ])
                ->default('draft')
                ->required(),

            DatePicker::make('published_at')
                ->label(__('Publish Date'))
                ->showTime(),
        ]);
    }
}
