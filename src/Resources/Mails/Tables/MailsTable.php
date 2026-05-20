<?php

namespace Ccast\TagixoPrimix\Resources\Mails\Tables;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoPrimix\Resources\Mails\MailResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;

class MailsTable
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
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable()
                    ->placeholder(__('No subject'))
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'draft'     => 'gray',
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'archived'  => 'danger',
                    ])
                    ->formatStateUsing(fn ($state) => $state instanceof PageStatus ? $state->label() : $state),

                TextColumn::make('published_at')
                    ->label(__('Published'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('Not published'))
                    ->toggleable(),

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
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                        'archived'  => __('Archived'),
                    ]),
            ])
            ->actions([
                EditAction::make(),

                Action::make('visualBuilder')
                    ->label(__('Visual Builder'))
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (MailTemplate $record) => MailResource::getUrl('build', ['record' => $record]))
                    ->openUrlInNewTab(),

                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (MailTemplate $record) {
                        $duplicate               = $record->replicate();
                        $duplicate->name         = $record->name . ' ' . __('(Copy)');
                        $duplicate->slug         = $record->slug . '-copy-' . time();
                        $duplicate->status       = PageStatus::Draft;
                        $duplicate->published_at = null;
                        $duplicate->save();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading(__('No mail templates yet'))
            ->emptyStateDescription(__('Create your first mail template to start sending email.'))
            ->emptyStateIcon('heroicon-o-envelope');
    }
}
