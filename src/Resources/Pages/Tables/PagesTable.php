<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Tables;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Models\Layout;
use Ccast\Tagixo\Models\Page;
use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Page $record): string => $record->url)
                    ->weight('medium'),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'draft'     => 'gray',
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'archived'  => 'danger',
                    ])
                    ->formatStateUsing(fn ($state) => $state instanceof PageStatus ? $state->label() : $state),

                TextColumn::make('layout_label')
                    ->label(__('Layout'))
                    ->getStateUsing(function (Page $record): string {
                        if ($record->layout?->name) {
                            return $record->layout->name;
                        }

                        static $globalLayoutName     = null;
                        static $globalLayoutResolved = false;

                        if (! $globalLayoutResolved) {
                            $globalLayoutName     = Layout::global()?->name;
                            $globalLayoutResolved = true;
                        }

                        if ($globalLayoutName) {
                            return __('Global: :name', ['name' => $globalLayoutName]);
                        }

                        return __('No layout');
                    })
                    ->badge()
                    ->color(fn (Page $record): string => $record->layout_id ? 'primary' : 'gray')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label(__('Published'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('Not published'))
                    ->toggleable(),

                TextColumn::make('parent.title')
                    ->label(__('Parent'))
                    ->placeholder(__('Root'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                        'archived'  => __('Archived'),
                    ]),

                SelectFilter::make('layout_id')
                    ->label(__('Layout'))
                    ->relationship('layout', 'name')
                    ->searchable(),
            ])
            ->actions([
                EditAction::make(),

                VisualBuilderAction::make(fn (Page $record) => PageResource::getUrl('build', ['record' => $record])),

                Action::make('publish')
                    ->label(__('Publish'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Page $record) => $record->publish())
                    ->visible(fn (Page $record) => $record->status !== PageStatus::Published),

                Action::make('unpublish')
                    ->label(__('Unpublish'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Page $record) => $record->unpublish())
                    ->visible(fn (Page $record) => $record->status === PageStatus::Published),

                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Page $record) {
                        $duplicate               = $record->replicate();
                        $duplicate->title        = $record->title . ' ' . __('(Copy)');
                        $duplicate->slug         = $record->slug . '-copy-' . time();
                        $duplicate->status       = PageStatus::Draft;
                        $duplicate->published_at = null;
                        $duplicate->save();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),

                Action::make('publishBulk')
                    ->label(__('Publish Selected'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->publish();
                        }
                    }),

                Action::make('unpublishBulk')
                    ->label(__('Unpublish Selected'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->unpublish();
                        }
                    }),
            ])
            ->emptyStateHeading(__('No pages yet'))
            ->emptyStateDescription(__('Create your first page to get started'))
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
