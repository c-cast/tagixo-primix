<?php

namespace Ccast\TagixoPrimix\Resources\Pdfs\Tables;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Models\PdfTemplate;
use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;
use Primix\Actions\Action;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;

class PdfsTable
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

                BadgeColumn::make('paper_size')
                    ->label(__('Paper'))
                    ->colors([
                        'A4' => 'info',
                        'A3' => 'info',
                        'A5' => 'info',
                        'letter' => 'gray',
                        'legal' => 'gray',
                    ])
                    ->toggleable(),

                BadgeColumn::make('orientation')
                    ->label(__('Orientation'))
                    ->colors([
                        'portrait' => 'gray',
                        'landscape' => 'gray',
                    ])
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'archived' => 'danger',
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
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                        'archived' => __('Archived'),
                    ]),
            ])
            ->actions([
                EditAction::make(),

                Action::make('visualBuilder')
                    ->label(__('Visual Builder'))
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (PdfTemplate $record) => PdfResource::getUrl('build', ['record' => $record]))
                    ->openUrlInNewTab(),

                Action::make('download')
                    ->label(__('Download PDF'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (PdfTemplate $record) => url('/admin/pdfs/' . $record->getKey() . '/download'))
                    ->openUrlInNewTab(),

                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (PdfTemplate $record) {
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
            ->emptyStateHeading(__('No PDF templates yet'))
            ->emptyStateDescription(__('Create your first PDF template to start generating documents.'))
            ->emptyStateIcon('heroicon-o-document');
    }
}
