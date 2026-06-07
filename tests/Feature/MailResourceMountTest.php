<?php

use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoPrimix\Tests\Support\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MailResource mount', function () {
    it('lists mail templates at /admin/mails', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.mails.index', [], false) ?: '/admin/mails';
        $response = $this->get($url);

        $response->assertOk();
    });

    it('shows the create mail form at /admin/mails/create', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.mails.create', [], false) ?: '/admin/mails/create';
        $response = $this->get($url);

        $response->assertOk();
    });

    it('shows the edit mail form at /admin/mails/{id}/edit', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mail = MailTemplate::create([
            'name' => 'Welcome',
            'slug' => 'welcome',
            'content' => ['components' => [], 'body' => []],
            'status' => 'draft',
        ]);

        $url = route('primix.admin.mails.edit', ['record' => $mail->getKey()], false) ?: "/admin/mails/{$mail->getKey()}/edit";
        $response = $this->get($url);

        $response->assertOk();
    });

    it('opens the visual builder at /admin/mails/{id}/build', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mail = MailTemplate::create([
            'name' => 'Welcome',
            'slug' => 'welcome',
            'content' => ['components' => [], 'body' => []],
            'status' => 'draft',
        ]);

        $url = route('primix.admin.mails.build', ['record' => $mail->getKey()], false) ?: "/admin/mails/{$mail->getKey()}/build";
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('context');
    });
});
