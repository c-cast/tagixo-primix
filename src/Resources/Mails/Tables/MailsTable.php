<?php

namespace Ccast\TagixoPrimix\Resources\Mails\Tables;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Facades\Tagixo;
use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoPrimix\Actions\VisualBuilderAction;
use Ccast\TagixoPrimix\Resources\Mails\MailResource;
use Primix\Actions\Action;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Notifications\Notification;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Filters\SelectFilter;
use Primix\Tables\Table;
use Throwable;

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

                VisualBuilderAction::make(fn (MailTemplate $record) => MailResource::getUrl('build', ['record' => $record])),

                Action::make('sendTest')
                    ->label(__('Send test'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->form([
                        TextInput::make('recipient')
                            ->label(__('Recipient'))
                            ->email()
                            ->required()
                            ->placeholder('you@example.com'),

                        Textarea::make('test_vars')
                            ->label(__('Test variables (JSON)'))
                            ->rows(4)
                            ->placeholder('{"name": "John"}')
                            ->helperText(__('Optional JSON object of variables interpolated into the template.')),
                    ])
                    ->action(function (MailTemplate $record, array $data) {
                        $vars = [];
                        $raw = trim((string) ($data['test_vars'] ?? ''));

                        if ($raw !== '') {
                            $decoded = json_decode($raw, true);
                            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                                Notification::make()
                                    ->title(__('Invalid JSON in test variables'))
                                    ->body(json_last_error_msg())
                                    ->danger()
                                    ->send();

                                return;
                            }
                            $vars = $decoded;
                        }

                        $subject = $record->subject ?: '[TEST] '.$record->name;

                        try {
                            $pending = Tagixo::mail($record->slug)
                                ->to($data['recipient'])
                                ->subject($subject);

                            if (! empty($vars)) {
                                $pending->with($vars);
                            }

                            $pending->send();
                        } catch (Throwable $e) {
                            report($e);

                            Notification::make()
                                ->title(__('Send failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('Test email sent to :recipient', ['recipient' => $data['recipient']]))
                            ->success()
                            ->send();
                    }),

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
