<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Concerns;

use Ccast\TagixoPrimix\Support\MenuTreeStructure;

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

    /** Move an item (with its subtree) above its previous sibling. */
    public function menuTreeMoveUp(int $index): void
    {
        $items = $this->menuTreeItems();
        $prev = $this->previousSiblingIndex($items, $index);

        if ($prev === null) {
            return;
        }

        $length = $this->blockLength($items, $index);
        $block = array_splice($items, $index, $length);
        array_splice($items, $prev, 0, $block);
        $this->setMenuTreeItems($items);
    }

    /** Move an item (with its subtree) below its next sibling. */
    public function menuTreeMoveDown(int $index): void
    {
        $items = $this->menuTreeItems();

        if (! array_key_exists($index, $items)) {
            return;
        }

        $depth = (int) ($items[$index]['depth'] ?? 0);
        $length = $this->blockLength($items, $index);
        $next = $index + $length;

        // A next sibling exists only if the following block is at the same depth.
        if (! array_key_exists($next, $items) || (int) ($items[$next]['depth'] ?? 0) !== $depth) {
            return;
        }

        $nextLength = $this->blockLength($items, $next);
        $block = array_splice($items, $index, $length);
        array_splice($items, $index + $nextLength, 0, $block);
        $this->setMenuTreeItems($items);
    }

    /** Nest an item (with its subtree) under its previous sibling. */
    public function menuTreeIndent(int $index): void
    {
        $items = $this->menuTreeItems();

        if ($this->previousSiblingIndex($items, $index) === null) {
            return;
        }

        $length = $this->blockLength($items, $index);
        for ($k = $index; $k < $index + $length; $k++) {
            $items[$k]['depth'] = (int) ($items[$k]['depth'] ?? 0) + 1;
        }

        $this->setMenuTreeItems($items);
    }

    /** Un-nest an item (with its subtree) by one level. */
    public function menuTreeOutdent(int $index): void
    {
        $items = $this->menuTreeItems();

        if (! array_key_exists($index, $items) || (int) ($items[$index]['depth'] ?? 0) <= 0) {
            return;
        }

        $length = $this->blockLength($items, $index);
        for ($k = $index; $k < $index + $length; $k++) {
            $items[$k]['depth'] = max(0, (int) ($items[$k]['depth'] ?? 0) - 1);
        }

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

        return is_array($items) ? array_values($items) : [];
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

    /**
     * Index of the previous sibling (same depth, same parent) of $index, or
     * null if it is the first child of its parent.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function previousSiblingIndex(array $items, int $index): ?int
    {
        if (! array_key_exists($index, $items)) {
            return null;
        }

        $depth = (int) ($items[$index]['depth'] ?? 0);

        for ($j = $index - 1; $j >= 0; $j--) {
            $dj = (int) ($items[$j]['depth'] ?? 0);

            if ($dj < $depth) {
                return null; // reached the parent → no previous sibling
            }

            if ($dj === $depth) {
                return $j;
            }
            // deeper → still inside the previous sibling's subtree, keep scanning
        }

        return null;
    }
}
