<?php

use Ccast\Tagixo\Models\FormSchema;
use Ccast\TagixoPrimix\Forms\PrimixFormStyles;
use Ccast\TagixoPrimix\Support\TagixoFormToPrimix;
use Illuminate\Foundation\Testing\RefreshDatabase;

// ---------------------------------------------------------------------------
// TagixoFormToPrimix — data-tgx-field scoping
// ---------------------------------------------------------------------------

describe('TagixoFormToPrimix', function () {
    beforeEach(function () {
        $this->converter = new TagixoFormToPrimix();
    });

    it('adds extraWrapperAttributes with data-tgx-field for leaf fields', function () {
        $defs = $this->converter->toDefinitions([[
            'id' => 'a1', 'type' => 'text-input', 'parent_id' => null, 'order' => 0,
            'props' => ['content' => ['name' => 'plan', 'label' => 'Plan']],
        ]]);

        expect($defs[0]['extraWrapperAttributes']['data-tgx-field'])->toBe('plan');
    });

    it('slugifies the field name for data-tgx-field', function () {
        $defs = $this->converter->toDefinitions([[
            'id' => 'a1', 'type' => 'text-input', 'parent_id' => null, 'order' => 0,
            'props' => ['content' => ['name' => 'My Field Name', 'label' => 'My Field']],
        ]]);

        expect($defs[0]['extraWrapperAttributes']['data-tgx-field'])->toBe('my_field_name');
    });

    it('does not add extraWrapperAttributes to layout containers', function (string $type) {
        $defs = $this->converter->toDefinitions([[
            'id' => 'g1', 'type' => $type, 'parent_id' => null, 'order' => 0,
            'props' => ['content' => ['label' => 'Container']],
        ]]);

        expect($defs)->not->toBeEmpty()
            ->and($defs[0])->not->toHaveKey('extraWrapperAttributes');
    })->with(['grid', 'section', 'fieldset']);

    it('does not add extraWrapperAttributes to tabs-layout', function () {
        $defs = $this->converter->toDefinitions([
            ['id' => 't1', 'type' => 'tabs-layout', 'parent_id' => null, 'order' => 0,
             'props' => ['content' => ['label' => 'Tabs']]],
            ['id' => 'tab1', 'type' => 'tab', 'parent_id' => 't1', 'order' => 0,
             'props' => ['content' => ['label' => 'Tab 1']]],
        ]);

        expect($defs[0])->not->toHaveKey('extraWrapperAttributes');
    });

    it('sets data-tgx-field on every leaf field inside a nested grid', function () {
        $defs = $this->converter->toDefinitions([
            ['id' => 'g1', 'type' => 'grid', 'parent_id' => null, 'order' => 0,
             'props' => ['content' => []]],
            ['id' => 'f1', 'type' => 'text-input', 'parent_id' => 'g1', 'order' => 0,
             'props' => ['content' => ['name' => 'plan', 'label' => 'Plan']]],
            ['id' => 'f2', 'type' => 'text-input', 'parent_id' => 'g1', 'order' => 1,
             'props' => ['content' => ['name' => 'price', 'label' => 'Price']]],
        ]);

        expect($defs[0]['schema'][0]['extraWrapperAttributes']['data-tgx-field'])->toBe('plan')
            ->and($defs[0]['schema'][1]['extraWrapperAttributes']['data-tgx-field'])->toBe('price');
    });
});

// ---------------------------------------------------------------------------
// PrimixFormStyles::scriptFrom — JS injection output
// ---------------------------------------------------------------------------

describe('PrimixFormStyles::scriptFrom', function () {
    uses(RefreshDatabase::class);

    it('returns empty string when form slug does not exist', function () {
        expect(PrimixFormStyles::scriptFrom('nonexistent'))->toBe('');
    });

    it('returns a script tag that injects CSS into head when form has element styles', function () {
        FormSchema::create([
            'slug'    => 'test-form',
            'title'   => 'Test',
            'content' => ['components' => [[
                'id' => 'a1', 'type' => 'text-input', 'parent_id' => null, 'order' => 0,
                'props' => [
                    'content'  => ['name' => 'plan', 'label' => 'Plan'],
                    'elements' => ['label' => ['typography' => ['text' => ['color' => '#ff0000', 'text_fill' => 'solid']]]],
                ],
            ]]],
        ]);

        $output = PrimixFormStyles::scriptFrom('test-form');

        expect($output)
            ->toContain('<script>')
            ->toContain("document.createElement('style')")
            ->toContain('document.head.appendChild')
            ->toContain('tgx-form-styles-test-form')
            ->toContain('#ff0000');
    });

    it('script contains getElementById check for idempotency', function () {
        FormSchema::create([
            'slug'    => 'test-form',
            'title'   => 'Test',
            'content' => ['components' => [[
                'id' => 'a1', 'type' => 'text-input', 'parent_id' => null, 'order' => 0,
                'props' => [
                    'content'  => ['name' => 'plan', 'label' => 'Plan'],
                    'elements' => ['label' => ['typography' => ['text' => ['color' => '#ff0000', 'text_fill' => 'solid']]]],
                ],
            ]]],
        ]);

        expect(PrimixFormStyles::scriptFrom('test-form'))->toContain('getElementById');
    });

    it('returns empty string when form components have no element styles', function () {
        FormSchema::create([
            'slug'    => 'test-form',
            'title'   => 'Test',
            'content' => ['components' => [[
                'id' => 'a1', 'type' => 'text-input', 'parent_id' => null, 'order' => 0,
                'props' => ['content' => ['name' => 'plan', 'label' => 'Plan'], 'elements' => []],
            ]]],
        ]);

        expect(PrimixFormStyles::scriptFrom('test-form'))->toBe('');
    });
});
