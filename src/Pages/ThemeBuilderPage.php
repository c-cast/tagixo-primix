<?php

namespace Ccast\TagixoPrimix\Pages;

use Ccast\Tagixo\Facades\Tagixo;
use Ccast\Tagixo\Models\Layout;
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
        $models = Tagixo::getRegisteredModels();
        $tree = [];
        foreach ($models as $key => $model) {
            $isTaxonomy = Tagixo::isTaxonomy($key);
            $tree[$key] = [
                'key'         => $key,
                'label'       => $model['label'],
                'is_taxonomy' => $isTaxonomy,
                'taxonomies'  => $isTaxonomy ? [] : Tagixo::getTaxonomiesFor($key),
            ];
        }

        return $tree;
    }

    private function likeOperator(): string
    {
        return \DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    public function searchPages(string $query): array
    {
        $op = $this->likeOperator();

        return \Ccast\Tagixo\Models\Page::where('title', $op, '%'.$query.'%')
            ->orWhere('slug', $op, '%'.$query.'%')
            ->limit(10)
            ->get(['id', 'title'])
            ->map(fn ($p) => ['id' => $p->id, 'title' => $p->title])
            ->toArray();
    }

    public function searchRecords(string $modelKey, string $query): array
    {
        $registration = Tagixo::getRegisteredModel($modelKey);
        if ($registration === null) {
            return [];
        }

        $op = $this->likeOperator();
        $class = $registration['class'];
        foreach (['title', 'name', 'label', 'slug'] as $col) {
            try {
                $results = $class::where($col, $op, '%'.$query.'%')->limit(10)->get(['id', $col]);

                return $results->map(fn ($r) => ['id' => $r->id, 'label' => $r->{$col}])->toArray();
            } catch (\Throwable) {
                continue;
            }
        }

        return [];
    }

    public function searchTaxonomyTerms(string $taxKey, string $query): array
    {
        $taxonomy = Tagixo::getRegisteredTaxonomies()[$taxKey] ?? null;
        if ($taxonomy === null) {
            return [];
        }

        $op = $this->likeOperator();
        $class = $taxonomy['class'];
        foreach (['title', 'name', 'label', 'slug'] as $col) {
            try {
                $results = $class::where($col, $op, '%'.$query.'%')->limit(10)->get(['id', $col]);

                return $results->map(fn ($r) => ['id' => $r->id, 'label' => $r->{$col}])->toArray();
            } catch (\Throwable) {
                continue;
            }
        }

        return [];
    }

    public function getConditionLabel(array $condition): string
    {
        $type = $condition['type'] ?? '';

        return match ($type) {
            'homepage'      => __('Homepage'),
            'page_id'       => $condition['label'] ?? (__('Page #').($condition['value'] ?? '?')),
            'model_all'     => __('All').' '.$this->modelLabel($condition['model'] ?? ''),
            'model_archive' => __('Archive').' '.$this->modelLabel($condition['model'] ?? ''),
            'model_taxonomy' => ($condition['term_label'] ?? ('#'.($condition['term_id'] ?? '?')))
                                .' ('.$this->modelLabel($condition['taxonomy'] ?? '').')',
            'model_record'  => $condition['record_label'] ?? (__('Record #').($condition['model_id'] ?? '?')),
            'all_pages'     => __('All Pages'),
            'template_type' => match ($condition['value'] ?? '') {
                'static'   => __('Static Pages'),
                'single'   => __('Single Pages'),
                'archive'  => __('Archive Pages'),
                'specific' => __('Specific Pages'),
                default    => ucfirst((string) ($condition['value'] ?? '')),
            }.(! empty($condition['model_class']) ? ' ('.class_basename($condition['model_class']).')' : ''),
            default         => ucfirst($type),
        };
    }

    private function modelLabel(string $key): string
    {
        if ($key === '') {
            return '';
        }
        $reg = Tagixo::getRegisteredModel($key);
        if ($reg) {
            return $reg['label'];
        }

        return Tagixo::getRegisteredTaxonomies()[$key]['label'] ?? $key;
    }

    protected function render(): string
    {
        return 'tagixo-primix::pages.theme-builder';
    }
}
