<?php

namespace Ccast\TagixoPrimix\Resources\Sliders;

use Ccast\Tagixo\Models\Slider;
use Ccast\TagixoPrimix\Resources\Sliders\Pages\EditSlider;
use Ccast\TagixoPrimix\Resources\Sliders\Pages\ListSliders;
use Ccast\TagixoPrimix\Resources\Sliders\Schemas\SliderForm;
use Ccast\TagixoPrimix\Resources\Sliders\Tables\SlidersTable;
use Primix\Forms\Form;
use Primix\Resources\Resource;
use Primix\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Visual Builder';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getModelLabel(): string
    {
        return __('Slider');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sliders');
    }

    public static function form(Form $form): Form
    {
        return SliderForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SlidersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Panel pages are metadata-only. The visual slider builder lives at the
     * plugin route `/builder/sliders/{id}/edit` and is opened in a new tab
     * via header/row actions (see ListSliders + SlidersTable).
     */
    public static function getPages(): array
    {
        return [
            'index' => ListSliders::route('/'),
            'edit'  => EditSlider::route('/{record}/edit'),
        ];
    }
}
