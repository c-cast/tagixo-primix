<?php

namespace Ccast\TagixoPrimix\Resources\Mails\Schemas;

use Primix\Forms\Components\Fields\DatePicker;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Form;

class MailForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label(__('Name'))
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->maxLength(255)
                ->suffixIcon('heroicon-o-link')
                ->placeholder(__('Slug')),

            TextInput::make('subject')
                ->label(__('Subject'))
                ->maxLength(255)
                ->helperText(__('Default subject used when sending this template. Can be overridden at send time.')),

            Textarea::make('preheader')
                ->label(__('Preheader'))
                ->rows(2)
                ->maxLength(255)
                ->helperText(__('Short summary shown after the subject in most inbox clients.')),

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
        ]);
    }
}
