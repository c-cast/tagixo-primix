<?php

namespace Ccast\TagixoPrimix\Resources\GlobalBlocks\Tables;

use Ccast\Tagixo\Models\GlobalBlock;
use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\GlobalBlockResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;

class GlobalBlocksTable
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

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('category')
                    ->label(__('Category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
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
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'archived' => __('Archived'),
                    ]),
            ])
            ->actions([
                VisualBuilderAction::make(fn (GlobalBlock $record): string => route('builder.global-blocks.edit', $record->id)
                    .'?back='.urlencode(GlobalBlockResource::getUrl('index'))),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading(__('No global blocks yet'))
            ->emptyStateDescription(__('Save an element as global from the builder to manage it here.'))
            ->emptyStateIcon('heroicon-o-globe-alt');
    }
}
