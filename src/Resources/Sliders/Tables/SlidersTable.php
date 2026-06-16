<?php

namespace Ccast\TagixoPrimix\Resources\Sliders\Tables;

use Ccast\Tagixo\Models\Slider;
use Ccast\TagixoPrimix\Resources\Sliders\SliderResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;

class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'draft'     => 'gray',
                        'published' => 'success',
                        'archived'  => 'danger',
                    ]),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'published' => __('Published'),
                        'archived'  => __('Archived'),
                    ]),
            ])
            ->actions([
                Action::make('visualBuilder')
                    ->label(__('Visual Builder'))
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (Slider $record): string => route('builder.sliders.edit', $record->id)
                        . '?back=' . urlencode(SliderResource::getUrl('index')))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading(__('No sliders yet'))
            ->emptyStateDescription(__('Create your first slider to embed it on pages.'))
            ->emptyStateIcon('heroicon-o-rectangle-group');
    }
}
