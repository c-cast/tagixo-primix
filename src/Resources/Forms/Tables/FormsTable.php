<?php

namespace Ccast\TagixoPrimix\Resources\Forms\Tables;

use Ccast\Tagixo\Models\FormSchema;
use Ccast\Tagixo\Tagixo;
use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        $locked = app(Tagixo::class)->getLockedFormTarget();

        $columns = [
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
        ];

        // Show form_target badge only when the panel is not locked to a single target
        if ($locked === null) {
            $columns[] = BadgeColumn::make('form_target')
                ->label(__('Target'))
                ->colors([
                    'universal' => 'gray',
                    'app'       => 'primary',
                ])
                ->toggleable();
        }

        $filters = [
            SelectFilter::make('status')
                ->label(__('Status'))
                ->options([
                    'draft'     => __('Draft'),
                    'published' => __('Published'),
                    'archived'  => __('Archived'),
                ]),
        ];

        // Show form_target filter only when the panel is not locked
        if ($locked === null) {
            $filters[] = SelectFilter::make('form_target')
                ->label(__('Target'))
                ->options([
                    'universal' => __('Universal'),
                    'app'       => __('App only'),
                ]);
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions([
                VisualBuilderAction::make(fn (FormSchema $record): string => route('builder.forms.edit', $record->id)
                    . '?back=' . urlencode(FormResource::getUrl('index'))),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading(__('No forms yet'))
            ->emptyStateDescription(__('Create your first form to embed it on pages.'))
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
