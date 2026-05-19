<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Schemas;

use Ccast\Tagixo\Models\Layout;
use Primix\Forms\Components\Fields\DatePicker;
use Primix\Forms\Components\Fields\FileUpload;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Form;

class PageForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label(__('Title'))
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->maxLength(255)
                ->suffixIcon('heroicon-o-link')
                ->placeholder(__('Slug')),

            Select::make('status')
                ->label(__('Status'))
                ->options([
                    'draft'     => __('Draft'),
                    'published' => __('Published'),
                    'scheduled' => __('Scheduled'),
                    'archived'  => __('Archived'),
                ])
                ->default('draft')
                ->required(),

            DatePicker::make('published_at')
                ->label(__('Publish Date'))
                ->showTime(),

            Select::make('template')
                ->label(__('Template'))
                ->options([
                    'default' => __('Default'),
                    'landing' => __('Landing Page'),
                    'contact' => __('Contact'),
                    'about'   => __('About'),
                    'product' => __('Product'),
                ])
                ->default('default')
                ->required(),

            Select::make('theme')
                ->label(__('Theme'))
                ->options([
                    'default' => __('Default Theme'),
                    'dark'    => __('Dark Theme'),
                    'minimal' => __('Minimal Theme'),
                ])
                ->nullable(),

            Select::make('parent_id')
                ->label(__('Parent Page'))
                ->relationship('parent', 'title')
                ->searchable()
                ->nullable(),

            Select::make('layout_id')
                ->label(__('Layout'))
                ->options(fn () => Layout::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText(__('If empty, the global layout will be used.')),

            Textarea::make('excerpt')
                ->label(__('Excerpt'))
                ->rows(3)
                ->maxLength(500),

            TextInput::make('meta_title')
                ->label(__('Meta Title'))
                ->maxLength(60),

            Textarea::make('meta_description')
                ->label(__('Meta Description'))
                ->rows(3)
                ->maxLength(160),

            FileUpload::make('og_image')
                ->label(__('OpenGraph Image'))
                ->image()
                ->maxSize(2048),
        ]);
    }
}
