<?php

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoPrimix\Support\MenuTreeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function menuTreeHarness(): object
{
    return new class
    {
        use PersistsMenuItems;

        public function persist(Menu $menu, array $items): void
        {
            $this->persistMenuItems($menu, $items);
        }

        public function tree(Menu $menu): array
        {
            return $this->menuItemsToTree($menu);
        }
    };
}

describe('MenuTreeStructure conversions', function () {
    it('flattens a nested tree with correct depths', function () {
        $tree = [
            ['label' => 'A', 'children' => [
                ['label' => 'A1', 'children' => [
                    ['label' => 'A1a', 'children' => []],
                ]],
                ['label' => 'A2', 'children' => []],
            ]],
            ['label' => 'B', 'children' => []],
        ];

        $flat = MenuTreeStructure::treeToFlat($tree);

        expect(array_column($flat, 'label'))->toBe(['A', 'A1', 'A1a', 'A2', 'B']);
        expect(array_column($flat, 'depth'))->toBe([0, 1, 2, 1, 0]);
        // children must not leak into the flat items
        expect($flat[0])->not->toHaveKey('children');
    });

    it('rebuilds a nested tree from a flat depth list (round-trip)', function () {
        $tree = [
            ['label' => 'A', 'children' => [
                ['label' => 'A1', 'children' => [
                    ['label' => 'A1a', 'children' => []],
                ]],
            ]],
            ['label' => 'B', 'children' => []],
        ];

        $rebuilt = MenuTreeStructure::flatToTree(MenuTreeStructure::treeToFlat($tree));

        expect($rebuilt)->toEqual($tree);
    });

    it('normalizes depths so the list is always a valid tree', function () {
        $flat = [
            ['label' => 'A', 'depth' => 5],   // first item → forced to 0
            ['label' => 'B', 'depth' => 3],   // jump from 0 → clamped to 1
            ['label' => 'C', 'depth' => -2],  // negative → 0
            ['label' => 'D', 'depth' => 2],   // from 0 → clamped to 1
        ];

        $depths = array_column(MenuTreeStructure::normalizeDepths($flat), 'depth');

        expect($depths)->toBe([0, 1, 0, 1]);
    });

    it('re-parents an orphaned child via normalization when building the tree', function () {
        // A child (depth 1) placed first is impossible; it must become a root.
        $flat = [
            ['label' => 'orphan', 'depth' => 1],
            ['label' => 'root', 'depth' => 0],
        ];

        $tree = MenuTreeStructure::flatToTree($flat);

        expect($tree)->toHaveCount(2);
        expect($tree[0]['label'])->toBe('orphan');
        expect($tree[0]['children'])->toBe([]);
    });
});

it('round-trips a 4-level deep menu through persistence', function () {
    $menu = Menu::create(['name' => 'Deep', 'slug' => 'deep']);
    $harness = menuTreeHarness();

    $flat = [
        ['label' => 'L0', 'target_type' => 'url', 'target_value' => '/', 'visible' => true, 'depth' => 0],
        ['label' => 'L1', 'target_type' => 'url', 'target_value' => '/a', 'visible' => true, 'depth' => 1],
        ['label' => 'L2', 'target_type' => 'url', 'target_value' => '/a/b', 'visible' => true, 'depth' => 2],
        ['label' => 'L3', 'target_type' => 'url', 'target_value' => '/a/b/c', 'visible' => true, 'depth' => 3],
        ['label' => 'Sibling', 'target_type' => 'url', 'target_value' => '/s', 'visible' => true, 'depth' => 0],
    ];

    // flat → tree → persist
    $harness->persist($menu, MenuTreeStructure::flatToTree($flat));

    expect($menu->allItems()->count())->toBe(5);

    // read back: tree → flat, and compare label/depth ordering
    $readFlat = MenuTreeStructure::treeToFlat($harness->tree($menu->fresh()));

    expect(array_column($readFlat, 'label'))->toBe(['L0', 'L1', 'L2', 'L3', 'Sibling']);
    expect(array_column($readFlat, 'depth'))->toBe([0, 1, 2, 3, 0]);
});

it('preserves the page-picker selection across a flat round-trip', function () {
    $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'content' => []]);
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main']);
    $harness = menuTreeHarness();

    $flat = [
        ['label' => 'About', 'target_type' => 'page', 'target_page_id' => $page->id, 'visible' => true, 'depth' => 0],
    ];

    $harness->persist($menu, MenuTreeStructure::flatToTree($flat));

    $readFlat = MenuTreeStructure::treeToFlat($harness->tree($menu->fresh()));

    expect($readFlat[0]['target_type'])->toBe('page');
    expect($readFlat[0]['target_page_id'])->toBe($page->id);
});
