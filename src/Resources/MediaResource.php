<?php

namespace Ccast\TagixoPrimix\Resources;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Ccast\Tagixo\MediaGallery\Services\MediaService;
use Ccast\TagixoPrimix\Resources\Media\Pages\ListMedia;
use Primix\Actions\Action;
use Primix\Forms\Components\Fields\FileUpload;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Form;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Actions\DeleteBulkAction;
use Primix\Resources\Actions\EditAction;
use Primix\Resources\Resource;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\ImageColumn;
use Primix\Tables\Columns\TextColumn;
use Primix\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): string
    {
        return __('Content');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('Media Gallery');
    }

    public static function getModelLabel(): string
    {
        return __('Media');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Media');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->label(__('Title'))->maxLength(255),
            TextInput::make('alt_text')->label(__('Alt Text'))->maxLength(255),
            Select::make('folder')
                ->label(__('Folder'))
                ->options(fn () => Media::query()->originals()->select('folder')->whereNotNull('folder')->distinct()->pluck('folder', 'folder')->toArray())
                ->searchable()
                ->nullable(),
            Textarea::make('description')->label(__('Description'))->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Media::query()->originals()->latest())
            ->columns([
                ImageColumn::make('path')
                    ->label(__('Preview'))
                    ->disk(fn (Media $record) => $record->disk)
                    ->height('80px')
                    ->square(),
                TextColumn::make('filename')
                    ->label(__('Filename'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('type')
                    ->label(__('Type'))
                    ->colors(['image' => 'success', 'video' => 'info', 'document' => 'warning', 'other' => 'gray']),
                TextColumn::make('formatted_size')
                    ->label(__('Size')),
                TextColumn::make('folder')
                    ->label(__('Folder'))
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label(__('Uploaded At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label(__('Upload Media'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('files')
                            ->label(__('Files'))
                            ->multiple()
                            ->maxFiles(10)
                            ->disk(config('visual-builder.media_gallery.disk', 'public'))
                            ->directory(config('visual-builder.media_gallery.storage_path', 'media'))
                            ->maxSize(config('visual-builder.media_gallery.max_file_size', 10240))
                            ->saveUploadedFileUsing(function ($file) {
                                return $file->store(
                                    config('visual-builder.media_gallery.storage_path', 'media'),
                                    config('visual-builder.media_gallery.disk', 'public')
                                );
                            })
                            ->required(),
                        TextInput::make('folder')->label(__('Folder'))->placeholder(__('Optional')),
                    ])
                    ->action(function (array $data): void {
                        $mediaService = app(MediaService::class);
                        $files = is_array($data['files']) ? $data['files'] : [$data['files']];

                        foreach ($files as $file) {
                            if ($file instanceof \Illuminate\Http\UploadedFile || $file instanceof \LiVue\Features\SupportFileUploads\TemporaryUploadedFile) {
                                $mediaService->upload(
                                    file: $file,
                                    disk: config('visual-builder.media_gallery.disk', 'public'),
                                    folder: $data['folder'] ?? null,
                                );
                            }
                        }
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->requiresConfirmation(),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
        ];
    }
}
