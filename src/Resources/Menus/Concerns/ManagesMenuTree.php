<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Concerns;

use Ccast\Tagixo\Support\MenuTreeStructure;

/**
 * LiVue methods + reactive state powering the WordPress-style menu tree on the
 * Create/Edit pages. The form state `data.items` is a FLAT list where each item
 * carries a `depth` (0 = top level); every mutation re-normalizes depths so the
 * list is always a valid tree. The drag gesture (SortableJS) calls
 * {@see menuTreeReorder}; everything else (add/remove/indent/outdent/move,
 * modal edit) is a plain server method, exactly like Primix's own Repeater.
 */
trait ManagesMenuTree
{
    /** Index of the item currently open in the edit modal (null = closed). */
    public ?int $editingItemIndex = null;

    /** Working copy of the item being edited; committed on save, dropped on cancel. */
    public array $editingItem = [];

    public function menuTreeAdd(): void
    {
        $items = $this->menuTreeItems();
        $items[] = MenuTreeStructure::blankItem();
        $this->setMenuTreeItems($items);

        // Open the new (empty) item straight away so the user gives it a label.
        $this->menuTreeOpenEdit(count($items) - 1);
    }

    /**
     * Remove an item and its whole subtree (the deeper items that follow it),
     * mirroring how deleting a parent removes its children in WordPress.
     */
    public function menuTreeRemove(int $index): void
    {
        $items = $this->menuTreeItems();

        if (! array_key_exists($index, $items)) {
            return;
        }

        $length = $this->blockLength($items, $index);
        array_splice($items, $index, $length);
        $this->setMenuTreeItems($items);
    }

    /**
     * Apply a new order coming from the drag gesture. The client sends only
     * {index, depth} pairs (original positions + target depth); we rebuild the
     * list from the existing items so no field data can be lost or forged.
     *
     * @param  array<int, array{index?: int|string, depth?: int|string}>  $order
     */
    public function menuTreeReorder(array $order): void
    {
        $items = $this->menuTreeItems();
        $reordered = [];

        foreach ($order as $entry) {
            $i = (int) ($entry['index'] ?? -1);

            if (! array_key_exists($i, $items)) {
                continue;
            }

            $node = $items[$i];
            $node['depth'] = max(0, (int) ($entry['depth'] ?? 0));
            $reordered[] = $node;
        }

        // Guard against a malformed payload silently dropping items.
        if (count($reordered) !== count($items)) {
            return;
        }

        $this->setMenuTreeItems($reordered);
    }

    public function menuTreeOpenEdit(int $index): void
    {
        $items = $this->menuTreeItems();

        if (! array_key_exists($index, $items)) {
            return;
        }

        $this->editingItemIndex = $index;
        $this->editingItem = array_merge(MenuTreeStructure::blankItem(), $items[$index]);
    }

    public function menuTreeSaveEdit(): void
    {
        if ($this->editingItemIndex === null) {
            $this->menuTreeCloseEdit();

            return;
        }

        $items = $this->menuTreeItems();
        $index = $this->editingItemIndex;

        if (array_key_exists($index, $items)) {
            // depth is owned by the tree (drag/indent), not the modal — preserve it.
            $depth = (int) ($items[$index]['depth'] ?? 0);
            $edited = $this->editingItem;
            $edited['depth'] = $depth;
            $items[$index] = $edited;
            $this->setMenuTreeItems($items);
        }

        $this->menuTreeCloseEdit();
    }

    public function menuTreeCloseEdit(): void
    {
        $this->editingItemIndex = null;
        $this->editingItem = [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function menuTreeItems(): array
    {
        $items = $this->data['items'] ?? [];
        $items = is_array($items) ? array_values($items) : [];

        // Assign stable Vue v-for keys to any item that lacks one (e.g. items
        // loaded from DB before the _key field was introduced). Keys persist
        // through reorder/edit so Vue can move DOM elements instead of
        // patching in-place, which fixes the SortableJS anchor-displacement bug.
        $needsSave = false;
        foreach ($items as &$item) {
            if (is_array($item) && empty($item['_key'])) {
                $item['_key'] = uniqid('mtk_', true);
                $needsSave = true;
            }
        }
        unset($item);

        if ($needsSave) {
            $this->data['items'] = $items;
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function setMenuTreeItems(array $items): void
    {
        $this->data['items'] = MenuTreeStructure::normalizeDepths(array_values($items));
    }

    /**
     * Number of items in the block rooted at $index (the item plus every
     * immediately-following item that is deeper than it).
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function blockLength(array $items, int $index): int
    {
        $depth = (int) ($items[$index]['depth'] ?? 0);
        $length = 1;
        $count = count($items);

        for ($j = $index + 1; $j < $count; $j++) {
            if ((int) ($items[$j]['depth'] ?? 0) > $depth) {
                $length++;
            } else {
                break;
            }
        }

        return $length;
    }
}
