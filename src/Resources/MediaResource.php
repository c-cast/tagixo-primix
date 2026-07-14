<?php

namespace Ccast\TagixoPrimix\Resources;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Ccast\Tagixo\MediaGallery\Services\MediaService;
use Ccast\TagixoPrimix\Resources\Media\Pages\ListMedia;
use Primix\Actions\Action;
use Primix\Forms\Components\Fields\FileUpload;
use Primix\Forms\Components\Fields\Placeholder;
use Primix\Forms\Components\Fields\Select;
use Primix\Forms\Components\Fields\Textarea;
use Primix\Forms\Components\Fields\TextInput;
use Primix\Forms\Components\Layouts\Grid;
use Primix\Forms\Components\Layouts\Tabs;
use Primix\Forms\Components\Layouts\Tabs\Tab;
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
            Grid::make(2)->schema([
                Placeholder::make('_preview')
                    ->label('')
                    ->columnSpan(1)
                    ->content(function ($record) {
                        if (! $record) {
                            return '';
                        }

                        $html = '<div class="flex flex-col gap-4">';

                        if ($record->isImage()) {
                            $html .= '<div class="overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">';
                            $html .= '<img src="'.e($record->url).'" alt="'.e($record->alt_text ?? $record->filename).'" class="w-full object-contain max-h-72" />';
                            $html .= '</div>';
                        } elseif ($record->isVideo()) {
                            $html .= '<div class="overflow-hidden rounded-xl bg-gray-900 flex items-center justify-center h-48">';
                            $html .= '<svg class="h-16 w-16 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
                            $html .= '</div>';
                        } else {
                            $html .= '<div class="flex h-32 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">';
                            $html .= '<span class="text-4xl font-bold text-gray-300 uppercase">'.e($record->extension).'</span>';
                            $html .= '</div>';
                        }

                        $html .= '<dl class="divide-y divide-gray-100 dark:divide-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">';

                        $rows = [
                            [__('Filename'), e($record->original_filename ?? $record->filename)],
                            [__('Type'), e($record->mime_type)],
                            [__('Size'), e($record->formatted_size)],
                        ];
                        if ($record->isImage() && $record->width && $record->height) {
                            $rows[] = [__('Dimensions'), $record->width.' × '.$record->height.' px'];
                        }
                        $rows[] = [__('Uploaded'), e($record->created_at->format('d M Y, H:i'))];
                        if ($record->folder) {
                            $rows[] = [__('Folder'), e($record->folder)];
                        }

                        foreach ($rows as [$label, $value]) {
                            $html .= '<div class="flex gap-3 px-3 py-2">';
                            $html .= '<dt class="w-24 shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400 pt-0.5">'.$label.'</dt>';
                            $html .= '<dd class="min-w-0 flex-1 text-xs text-gray-900 dark:text-gray-100 break-all">'.$value.'</dd>';
                            $html .= '</div>';
                        }

                        $html .= '</dl>';
                        $html .= '</div>';

                        return $html;
                    }),

                Tabs::make()
                    ->columnSpan(1)
                    ->tabs([
                        Tab::make(__('Details'))->schema([
                            TextInput::make('title')->label(__('Title'))->maxLength(255),
                            TextInput::make('alt_text')->label(__('Alt Text'))->maxLength(255),
                            Select::make('folder')
                                ->label(__('Folder'))
                                ->options(fn () => Media::query()->originals()->select('folder')->whereNotNull('folder')->distinct()->pluck('folder', 'folder')->toArray())
                                ->searchable()
                                ->nullable(),
                            Textarea::make('description')->label(__('Description'))->rows(3),
                        ]),

                        Tab::make(__('Crops'))
                            ->schema([
                                Placeholder::make('_crops')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (! $record) {
                                            return '<p class="text-sm text-gray-400">'.__('No crops available.').'</p>';
                                        }

                                        $crops = $record->crops()->get();
                                        if ($crops->isEmpty()) {
                                            return '<p class="text-sm text-gray-400 py-4 text-center">'.__('No crop variants found.').'</p>';
                                        }

                                        $html = '<div class="grid grid-cols-2 gap-3">';
                                        foreach ($crops as $crop) {
                                            $html .= '<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">';
                                            if ($crop->isImage()) {
                                                $html .= '<img src="'.e($crop->url).'" alt="'.e($crop->filename).'" class="w-full object-cover aspect-video" loading="lazy" />';
                                            }
                                            $html .= '<div class="flex items-center justify-between gap-1 px-2 py-1.5 bg-gray-50 dark:bg-gray-800">';
                                            $html .= '<div class="min-w-0">';
                                            $html .= '<p class="truncate text-xs font-medium text-gray-700 dark:text-gray-300">'.e($crop->filename).'</p>';
                                            if ($crop->width && $crop->height) {
                                                $html .= '<p class="text-[11px] text-gray-400">'.$crop->width.'×'.$crop->height.'</p>';
                                            }
                                            $html .= '</div>';
                                            $confirmMsg = addslashes(__('Delete this crop variant?'));
                                            $js = "(function(){var c=[...window.LiVue.components.values()].find(function(x){return x._rootLivue&&x._rootLivue._callableMethods&&x._rootLivue._callableMethods.includes('deleteCrop')});if(!c)return;c._rootLivue._showConfirm({message:'{$confirmMsg}'}).then(function(ok){if(ok)c._rootLivue.call('deleteCrop',[{$crop->id}])});})()";
                                            $html .= '<button onclick="'.e($js).'" type="button" class="shrink-0 rounded p-0.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="'.__('Delete').'">';
                                            $html .= '<svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                                            $html .= '</button>';
                                            $html .= '</div></div>';
                                        }
                                        $html .= '</div>';

                                        return $html;
                                    }),
                            ]),
                    ]),
            ]),
        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Media::query()->originals()->latest();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->grid(5)
            ->gridCardView('tagixo-primix::tables.media-grid-card')
            ->switchableLayout()
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
                EditAction::make()->modalWidth('max-w-7xl'),
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
            ->perPageOptions([25, 50, 100])
            ->defaultPerPage(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
        ];
    }
}
