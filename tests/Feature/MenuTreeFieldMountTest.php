<?php

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\MenuItem;
use Ccast\TagixoPrimix\Tests\Support\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MenuTreeField mount', function () {
    it('renders /admin/menus/create with the tree field', function () {
        $this->actingAs(User::factory()->create());

        $url = route('primix.admin.menus.create', [], false) ?: '/admin/menus/create';
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('data-menu-tree-list', false);
        // the legacy nested Repeater must be gone
        $response->assertDontSee('Add sub-item');
    });

    it('renders /admin/menus/{id}/edit for an existing nested menu', function () {
        $this->actingAs(User::factory()->create());

        $menu = Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu']);
        $parent = MenuItem::create([
            'menu_id' => $menu->id, 'parent_id' => null, 'label' => 'Parent',
            'target_type' => 'url', 'target_value' => '/', 'order' => 0, 'visible' => true,
        ]);
        MenuItem::create([
            'menu_id' => $menu->id, 'parent_id' => $parent->id, 'label' => 'Child',
            'target_type' => 'url', 'target_value' => '/c', 'order' => 0, 'visible' => true,
        ]);

        $url = route('primix.admin.menus.edit', ['record' => $menu->getKey()], false) ?: "/admin/menus/{$menu->getKey()}/edit";
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('data-menu-tree-list', false);
    });
});
