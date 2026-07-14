<?php

namespace Ccast\TagixoPrimix\Forms;

use Ccast\Tagixo\Models\FormSchema;
use Primix\Tables\Columns\BadgeColumn;
use Primix\Tables\Columns\BooleanColumn;
use Primix\Tables\Columns\TextColumn;

class PrimixFormColumns
{
    public static function from(string $formSlug): array
    {
        $form = FormSchema::where('slug', $formSlug)->first();

        if ($form === null) {
            return [];
        }

        return self::resolveColumns($form);
    }

    public static function forForm(int|string $formId): array
    {
        $form = FormSchema::find($formId);

        if ($form === null) {
            return [];
        }

        return self::resolveColumns($form);
    }

    private static function resolveColumns(FormSchema $form): array
    {
        $columns = [];

        foreach ($form->fields ?? [] as $field) {
            $tableProps = $field['props']['table'] ?? [];

            if (! (bool) ($tableProps['show_in_table']['value'] ?? false)) {
                continue;
            }

            $fieldKey = $field['key'] ?? $field['id'] ?? null;

            if ($fieldKey === null) {
                continue;
            }

            $columnLabel = (string) ($tableProps['column_label']['value'] ?? '');
            $columnLabel = $columnLabel ?: ($field['label'] ?? $fieldKey);
            $columnType  = (string) ($tableProps['column_type']['value'] ?? 'text');
            $sortable    = (bool) ($tableProps['sortable']['value'] ?? false);
            $searchable  = (bool) ($tableProps['searchable']['value'] ?? false);

            $column = match ($columnType) {
                'boolean' => BooleanColumn::make($fieldKey)->label($columnLabel),
                'badge'   => BadgeColumn::make($fieldKey)->label($columnLabel),
                'date'    => TextColumn::make($fieldKey)->label($columnLabel)->dateTime('d/m/Y H:i'),
                default   => TextColumn::make($fieldKey)->label($columnLabel),
            };

            if ($sortable) {
                $column = $column->sortable();
            }

            if ($searchable) {
                $column = $column->searchable();
            }

            $columns[] = $column;
        }

        return $columns;
    }
}
