<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Schemas;

use Ccast\TagixoPrimix\Support\SlugInput;
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
                    'draft'     => __('Draft'),
                    'published' => __('Published'),
                    'scheduled' => __('Scheduled'),
                    'archived'  => __('Archived'),
                ])
                ->default('draft')
                ->required(),

            DatePicker::make('published_at')
                ->label(__('Publish Date'))
                ->showTime()
                ->helperText(__('Leave empty to publish immediately. Otherwise the page goes live from this date.')),

            DatePicker::make('published_until')
                ->label(__('Publish Until'))
                ->showTime()
                ->helperText(__('Leave empty to keep it published indefinitely. Otherwise the page is hidden after this date.')),

            Select::make('parent_id')
                ->label(__('Parent Page'))
                ->relationship('parent', 'title')
                ->searchable()
                ->preload()
                ->nullable(),


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

    private static function resolveModelAttributes(?string $modelClass): array
    {
        if (! $modelClass || ! class_exists($modelClass)) {
            return ['id' => 'id'];
        }

        try {
            $instance = new $modelClass;
            $columns = $instance->getFillable();
            $result = ['id' => 'id'];
            foreach ($columns as $col) {
                $result[$col] = $col;
            }

            return $result;
        } catch (\Throwable) {
            return ['id' => 'id'];
        }
    }
}
