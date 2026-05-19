<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Concerns;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\MenuItem;
use Illuminate\Support\Collection;

trait PersistsMenuItems
{
    protected function persistMenuItems(Menu $menu, array $items): void
    {
        $menu->allItems()->delete();
        $this->createMenuItemsRecursive($menu, $items, null);
    }

    protected function createMenuItemsRecursive(Menu $menu, array $items, ?int $parentId): void
    {
        $order = 0;

        foreach ($items as $item) {
            $children = $item['children'] ?? [];
            $label = $item['label'] ?? null;

            if (! $label) {
                continue;
            }

            $payload = [
                'label' => $label,
                'target_type' => $item['target_type'] ?? 'url',
                'target_value' => $item['target_value'] ?? null,
                'target_meta' => $item['target_meta'] ?? null,
                'new_tab' => (bool) ($item['new_tab'] ?? false),
                'icon' => $item['icon'] ?? null,
                'css_class' => $item['css_class'] ?? null,
                'visible' => (bool) ($item['visible'] ?? true),
                'order' => $order,
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
            ];

            $menuItem = MenuItem::create($payload);
            $this->createMenuItemsRecursive($menu, is_array($children) ? $children : [], $menuItem->id);
            $order++;
        }
    }

    protected function menuItemsToTree(Menu $menu): array
    {
        $items = $menu->allItems()->get();
        $grouped = $items->groupBy('parent_id');

        return $this->buildSubtree($grouped, null);
    }

    private function buildSubtree(Collection $grouped, ?int $parentId): array
    {
        $items = $grouped->get($parentId, collect())->sortBy('order');

        return $items->map(function (MenuItem $item) use ($grouped) {
            $targetType = $item->target_type instanceof \BackedEnum
                ? $item->target_type->value
                : (string) $item->target_type;

            return [
                'label' => $item->label,
                'target_type' => $targetType,
                'target_value' => $item->target_value,
                'target_meta' => $item->target_meta,
                'new_tab' => (bool) $item->new_tab,
                'icon' => $item->icon,
                'css_class' => $item->css_class,
                'visible' => (bool) $item->visible,
                'children' => $this->buildSubtree($grouped, $item->id),
            ];
        })->values()->toArray();
    }
}
