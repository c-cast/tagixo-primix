<?php

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\PersistsMenuItems;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Exercises the PersistsMenuItems trait directly (the LiVue page lifecycle is
 * covered by the mount tests), focusing on the page-picker <-> target_value
 * folding added for the "Page" link type.
 */
function menuItemsHarness(): object
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

it('folds the page picker selection into target_value for page items', function () {
    $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'content' => []]);
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main']);

    menuItemsHarness()->persist($menu, [
        [
            'label' => 'About',
            'target_type' => 'page',
            'target_page_id' => $page->id,
            'target_value' => null,
            'visible' => true,
            'children' => [],
        ],
    ]);

    $item = $menu->allItems()->firstWhere('label', 'About');
    expect($item)->not->toBeNull();
    expect((string) $item->target_value)->toBe((string) $page->id);
});

it('keeps target_value for non-page items and ignores a stray page id', function () {
    $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'content' => []]);
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main']);

    menuItemsHarness()->persist($menu, [
        [
            'label' => 'External',
            'target_type' => 'url',
            'target_page_id' => $page->id,
            'target_value' => 'https://example.com',
            'visible' => true,
            'children' => [],
        ],
    ]);

    $item = $menu->allItems()->firstWhere('label', 'External');
    expect($item->target_value)->toBe('https://example.com');
});

it('re-derives target_page_id when reading page items back into the form', function () {
    $page = Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published', 'content' => []]);
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main']);
    $harness = menuItemsHarness();

    // Persisted as a slug (resolveUrl accepts both id and slug).
    $harness->persist($menu, [
        [
            'label' => 'About',
            'target_type' => 'page',
            'target_value' => 'about',
            'visible' => true,
            'children' => [],
        ],
    ]);

    $tree = $harness->tree($menu->fresh());
    expect($tree[0]['target_page_id'])->toBe($page->id);
});

it('persists nested children through the page-picker mapping', function () {
    $parentPage = Page::create(['title' => 'Services', 'slug' => 'services', 'status' => 'published', 'content' => []]);
    $childPage = Page::create(['title' => 'Consulting', 'slug' => 'consulting', 'status' => 'published', 'content' => []]);
    $menu = Menu::create(['name' => 'Main', 'slug' => 'main']);

    menuItemsHarness()->persist($menu, [
        [
            'label' => 'Services',
            'target_type' => 'page',
            'target_page_id' => $parentPage->id,
            'visible' => true,
            'children' => [
                [
                    'label' => 'Consulting',
                    'target_type' => 'page',
                    'target_page_id' => $childPage->id,
                    'visible' => true,
                ],
            ],
        ],
    ]);

    expect($menu->allItems()->count())->toBe(2);

    $parent = $menu->items()->firstWhere('label', 'Services');
    expect((string) $parent->target_value)->toBe((string) $parentPage->id);

    $child = $parent->children()->firstWhere('label', 'Consulting');
    expect($child)->not->toBeNull();
    expect((string) $child->target_value)->toBe((string) $childPage->id);
});
