<?php

use Ccast\Tagixo\Models\PdfTemplate;
use Ccast\TagixoPrimix\Tests\Support\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PdfResource mount', function () {
    it('lists pdf templates at /admin/pdfs', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.pdfs.index', [], false) ?: '/admin/pdfs';
        $response = $this->get($url);

        $response->assertOk();
    });

    it('shows the create pdf form at /admin/pdfs/create', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = route('primix.admin.pdfs.create', [], false) ?: '/admin/pdfs/create';
        $response = $this->get($url);

        $response->assertOk();
    });

    it('shows the edit pdf form at /admin/pdfs/{id}/edit', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pdf = PdfTemplate::create([
            'name' => 'Invoice',
            'slug' => 'invoice',
            'content' => ['components' => [], 'body' => []],
            'status' => 'draft',
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'margin' => '2cm',
        ]);

        $url = route('primix.admin.pdfs.edit', ['record' => $pdf->getKey()], false) ?: "/admin/pdfs/{$pdf->getKey()}/edit";
        $response = $this->get($url);

        $response->assertOk();
    });

    it('opens the visual builder at /admin/pdfs/{id}/build', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pdf = PdfTemplate::create([
            'name' => 'Invoice',
            'slug' => 'invoice',
            'content' => ['components' => [], 'body' => []],
            'status' => 'draft',
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'margin' => '2cm',
        ]);

        $url = route('primix.admin.pdfs.build', ['record' => $pdf->getKey()], false) ?: "/admin/pdfs/{$pdf->getKey()}/build";
        $response = $this->get($url);

        $response->assertOk();
    });
});
