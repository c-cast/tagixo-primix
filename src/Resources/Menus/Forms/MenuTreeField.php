<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Forms;

use Ccast\Tagixo\Enums\MenuItemTargetType;
use Ccast\Tagixo\Models\Page;
use Ccast\Tagixo\Support\MenuTreeStructure;
use Primix\Forms\Components\Fields\ViewField;

/**
 * WordPress-style menu builder field: a flat, depth-aware tree the user
 * reorders by drag (SortableJS) and re-levels with indent/outdent, editing
 * each item in a modal. The heavy lifting lives in the Blade view
 * (`tagixo-primix::forms.menu-tree`) + the {@see \Ccast\TagixoPrimix\Resources\Menus\Concerns\ManagesMenuTree}
 * trait on the page; this field just feeds the view its options.
 *
 * State shape: a flat list of items, each with a `depth` key
 * (see {@see MenuTreeStructure}). The Create/Edit pages convert to/from the
 * nested `children` shape the persistence layer expects.
 */
class MenuTreeField extends ViewField
{
    protected function setUp(): void
    {
        $this->view('tagixo-primix::forms.menu-tree');
        $this->default([]);
    }

    public function toVueProps(): array
    {
        return array_merge(parent::toVueProps(), [
            'linkTypeOptions' => static::linkTypeOptions(),
            'pageOptions' => static::pageOptions(),
            'blankItem' => MenuTreeStructure::blankItem(),
            'dropdownTypeOptions' => static::dropdownTypeOptions(),
            'iconSets' => app(\Ccast\Tagixo\Core\IconManager::class)->getIconSetsForVue(),
        ]);
    }

    /**
     * Link-type options in the {label, value} shape PrimeVue's <p-select> uses.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected static function linkTypeOptions(): array
    {
        $options = [];

        foreach (MenuItemTargetType::options() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * @return array<int, array{value: string|null, label: string}>
     */
    protected static function dropdownTypeOptions(): array
    {
        return [
            ['value' => null, 'label' => __('Dropdown (default)')],
            ['value' => 'mega', 'label' => __('Mega menu')],
        ];
    }

    /**
     * Page picker options: page id => "Title (slug)", in <p-select> shape.
     *
     * Mirrors the folding done by
     * {@see \Ccast\TagixoPrimix\Resources\Menus\Concerns\PersistsMenuItems}:
     * the selected id is stored on the item as target_page_id and folded into
     * target_value at persist time.
     *
     * @return array<int, array{value: int, label: string}>
     */
    protected static function pageOptions(): array
    {
        return Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->map(fn (Page $page) => [
                'value' => $page->id,
                'label' => trim(($page->title ?: __('Untitled')).' ('.$page->slug.')'),
            ])
            ->all();
    }
}
