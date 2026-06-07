<?php

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoPrimix\Tests\Support\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MenuForm Repeater mount', function () {
    it('renders /admin/menus/create without error', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.menus.create', [], false) ?: '/admin/menus/create';
        $response = $this->get($url);

        $response->assertOk();
    });

    it('renders /admin/menus/{id}/edit for an existing menu', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $menu = Menu::create([
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'items' => [],
        ]);

        $url = route('primix.admin.menus.edit', ['record' => $menu->getKey()], false) ?: "/admin/menus/{$menu->getKey()}/edit";
        $response = $this->get($url);

        $response->assertOk();
    });

    it('does not crash when the Repeater itemLabel is evaluated with no state', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.menus.create', [], false) ?: '/admin/menus/create';
        $response = $this->get($url);

        $response->assertOk();
        $response->assertDontSee('Select::live');
        $response->assertDontSee('Argument #1 ($state) must be of type array, null given');
    });
});
