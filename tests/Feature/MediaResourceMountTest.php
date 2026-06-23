<?php

use Ccast\TagixoPrimix\Tests\Support\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MediaResource mount', function () {
    it('lists media at /admin/media (renders the paginated table)', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.media.index', [], false) ?: '/admin/media';
        $response = $this->get($url);

        $response->assertOk();
    });
});
