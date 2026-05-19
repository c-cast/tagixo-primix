<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Tables;

use Ccast\Tagixo\Models\Menu;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->getStateUsing(fn (Menu $record): int => $record->allItems()->count())
                    ->badge()
                    ->color('primary'),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading(__('No menus yet'))
            ->emptyStateDescription(__('Create your first menu to use across the site.'))
            ->emptyStateIcon('heroicon-o-bars-3');
    }
}
