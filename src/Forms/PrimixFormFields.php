<?php

namespace Ccast\TagixoPrimix\Forms;

use Ccast\Tagixo\Models\FormSchema;
use Ccast\TagixoPrimix\Support\TagixoFormToPrimix;
use Primix\Support\SchemaBuilder;

class PrimixFormFields
{
    public static function from(string $formSlug): array
    {
        $form = FormSchema::where('slug', $formSlug)->first();

        return $form ? self::resolveFields($form) : [];
    }

    public static function forForm(int|string $formId): array
    {
        $form = FormSchema::find($formId);

        return $form ? self::resolveFields($form) : [];
    }

    private static function resolveFields(FormSchema $form): array
    {
        $content = is_array($form->content ?? null) ? $form->content : [];
        $components = is_array($content['components'] ?? null)
            ? $content['components']
            : (is_array($form->fields ?? null) ? $form->fields : []);

        // Read the root column count from body.grid.columns (set via FormLayoutPropType).
        // Default 12 matches the form builder default.
        $body = is_array($content['body'] ?? null) ? $content['body'] : [];
        $rootColumns = (int) ($body['grid']['columns']['value'] ?? $body['grid']['columns'] ?? 12);

        $definitions = app(TagixoFormToPrimix::class)->toDefinitions($components, $rootColumns);

        return app(SchemaBuilder::class)->build($definitions, 'field');
    }
}
