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
        // Body of a model-scoped template: create the special page lazily and
        // edit THAT page. Mirrors tagixo-filament's ThemeBuilderPage.
        if ($section === 'body') {
            $layout = Layout::find($layoutId);
            $target = $this->resolveModelPageTarget($layout?->conditions ?? []);

            if ($target !== null) {
                [$modelKey, $templateType] = $target;
                $page = \Ccast\Tagixo\Facades\Tagixo::ensureRoutePagesForModel($modelKey)[$templateType] ?? null;

                if ($page !== null) {
                    return \Ccast\TagixoPrimix\Resources\Pages\PageResource::getUrl('build', ['record' => $page]);
                }
            }
        }

        return LayoutResource::getUrl('build', ['record' => $layoutId, 'section' => $section]);
    }

    /**
     * Whether the template's Body is configured (model-scoped templates check
     * the special page's content). Mirrors tagixo-filament — keep in sync.
     */
    public function isBodyConfigured($layout): bool
    {
        $target = $this->resolveModelPageTarget($layout->conditions ?? []);

        if ($target !== null) {
            [$modelKey, $templateType] = $target;
            $page = \Ccast\Tagixo\Facades\Tagixo::findRoutePagesForModel($modelKey)[$templateType] ?? null;

            return $page !== null && ! empty($page->content['components'] ?? null);
        }

        return (bool) $layout->body_rendered_html;
    }

    /**
     * @param  array<int, mixed>  $conditions
     * @return array{0: string, 1: string}|null
     */
    protected function resolveModelPageTarget(array $conditions): ?array
    {
        foreach ($conditions as $condition) {
            if (! is_array($condition) || empty($condition['model'])) {
                continue;
            }

            $type = $condition['type'] ?? null;

            if ($type === 'model_archive') {
                return [(string) $condition['model'], 'archive'];
            }

            if (in_array($type, ['model_all', 'model_taxonomy', 'model_record'], true)) {
                return [(string) $condition['model'], 'single'];
            }
        }

        return null;
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
