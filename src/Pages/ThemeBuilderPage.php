<?php

namespace Ccast\TagixoPrimix\Pages;

use Ccast\Tagixo\Models\Layout;
use Ccast\Tagixo\Services\LayoutConditionService;
use Ccast\TagixoPrimix\Resources\LayoutResource;
use Illuminate\Support\Facades\Validator;
use Primix\Pages\Page;
use Primix\Support\Enums\Width;

class ThemeBuilderPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?Width $maxContentWidth = Width::ScreenTwoExtraLarge;

    protected static ?int $navigationSort = 15;

    public bool $showModal = false;

    public ?int $editingLayoutId = null;

    public static function getNavigationGroup(): string
    {
        return __('Visual Builder');
    }

    public static function getNavigationLabel(): string
    {
        return __('Theme Builder');
    }

    public function getTitle(): string
    {
        return __('Theme Builder');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getSlug(): string
    {
        return 'theme-builder';
    }

    public function getLayouts(): \Illuminate\Support\Collection
    {
        return Layout::orderByDesc('is_global')->orderBy('name')->get();
    }

    public function getRegisteredModels(): array
    {
        return Tagixo::getRegisteredModels();
    }

    public function openCreateModal(): void
    {
        $this->editingLayoutId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $layoutId): array
    {
        $layout = Layout::findOrFail($layoutId);

        $this->editingLayoutId = $layoutId;
        $this->showModal = true;

        return [
            'name'       => $layout->name,
            'conditions' => $layout->conditions ?? [],
        ];
    }

    public function saveModal(string $name, array $conditions = []): void
    {
        Validator::make(['name' => $name], ['name' => 'required|string|max:255'])->validate();

        if ($this->editingLayoutId) {
            Layout::findOrFail($this->editingLayoutId)->update([
                'name'       => $name,
                'conditions' => $conditions ?: null,
            ]);
        } else {
            Layout::create([
                'name'       => $name,
                'conditions' => $conditions ?: null,
            ]);
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingLayoutId = null;
    }

    public function deleteLayout(int $layoutId): void
    {
        Layout::findOrFail($layoutId)->delete();
    }

    public function getBuildUrl(int $layoutId, string $section): string
    {
        return LayoutResource::getUrl('build', ['record' => $layoutId, 'section' => $section]);
    }

    public function getConditionTree(): array
    {
        return app(LayoutConditionService::class)->getConditionTree();
    }

    public function searchPages(string $query): array
    {
        return app(LayoutConditionService::class)->searchPages($query);
    }

    public function searchRecords(string $modelKey, string $query): array
    {
        return app(LayoutConditionService::class)->searchRecords($modelKey, $query);
    }

    public function searchTaxonomyTerms(string $taxKey, string $query): array
    {
        return app(LayoutConditionService::class)->searchTaxonomyTerms($taxKey, $query);
    }

    public function getConditionLabel(array $condition): string
    {
        return app(LayoutConditionService::class)->getConditionLabel($condition);
    }

    protected function render(): string
    {
        return 'tagixo-primix::pages.theme-builder';
    }
}
