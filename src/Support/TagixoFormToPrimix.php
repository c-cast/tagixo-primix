<?php

namespace Ccast\TagixoPrimix\Support;

use Illuminate\Support\Str;

/**
 * Converts a saved Tagixo form schema (flat list of nodes with parent_id) into a
 * Primix Forms "fromSchema" definitions array (nested), so an app-target form can
 * be rendered as a real Primix form — with native interactive Tabs/Wizard.
 *
 * Tagixo type  → Primix type
 *   text-input → text-input,  text-area → textarea,  select → select
 *   checkbox → checkbox,  radio → radio,  date-picker → date-picker
 *   file-upload → file-upload
 *   grid/group → grid,  section → section,  fieldset → fieldset
 *   tabs-layout → tabs (children → 'tabs'),  wizard → wizard (children → 'steps')
 *   submit-button → omitted (preview form has no submit)
 */
class TagixoFormToPrimix
{
    private const TYPE_MAP = [
        'text-input' => 'text-input',
        'text-area' => 'textarea',
        'select' => 'select',
        'checkbox' => 'checkbox',
        'radio' => 'radio',
        'date-picker' => 'date-picker',
        'file-upload' => 'file-upload',
        'grid' => 'grid',
        'group' => 'grid',
        'section' => 'section',
        'fieldset' => 'fieldset',
        'tabs-layout' => 'tabs',
        'wizard' => 'wizard',
    ];

    /** @var array<string, array<int, array<string,mixed>>> */
    private array $childrenByParent = [];

    /**
     * @param  array<int, array<string,mixed>>  $components  flat Tagixo components
     * @return array<int, array<string,mixed>>  Primix fromSchema definitions
     */
    public function toDefinitions(array $components): array
    {
        $this->childrenByParent = [];
        foreach ($components as $component) {
            if (! is_array($component)) {
                continue;
            }
            $parent = $component['parent_id'] ?? null;
            $key = ($parent === null || $parent === '') ? '__root__' : (string) $parent;
            $this->childrenByParent[$key][] = $component;
        }
        foreach ($this->childrenByParent as &$siblings) {
            usort($siblings, static fn ($a, $b) => ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0)));
        }
        unset($siblings);

        return $this->buildLevel('__root__');
    }

    /** @return array<int, array<string,mixed>> */
    private function buildLevel(string $parentKey): array
    {
        $defs = [];
        // Orphan tab/wizard-step nodes (no tabs-layout/wizard parent — possible in
        // older malformed schemas) are grouped into a synthetic container so they
        // still render as real Tabs/Wizard.
        $pendingTabs = [];
        $pendingSteps = [];
        $flush = function () use (&$defs, &$pendingTabs, &$pendingSteps): void {
            if ($pendingTabs !== []) {
                $defs[] = ['type' => 'tabs', 'tabs' => $pendingTabs];
                $pendingTabs = [];
            }
            if ($pendingSteps !== []) {
                $defs[] = ['type' => 'wizard', 'steps' => $pendingSteps];
                $pendingSteps = [];
            }
        };

        foreach ($this->childrenByParent[$parentKey] ?? [] as $node) {
            $type = (string) ($node['type'] ?? '');

            if ($type === 'tab') {
                $pendingTabs[] = $this->branch($node, 'tab');
                continue;
            }
            if ($type === 'wizard-step') {
                $pendingSteps[] = $this->branch($node, 'step');
                continue;
            }

            $flush();
            $def = $this->buildNode($node);
            if ($def !== null) {
                $defs[] = $def;
            }
        }

        $flush();

        return $defs;
    }

    /** @return array<string,mixed>|null */
    private function buildNode(array $node): ?array
    {
        $type = (string) ($node['type'] ?? '');
        $id = (string) ($node['id'] ?? '');
        $props = $this->props($node);

        // Tab / wizard-step are emitted as entries of their parent's tabs/steps,
        // not as standalone components.
        if ($type === 'submit-button' || $type === 'tab' || $type === 'wizard-step') {
            return null;
        }

        $primixType = self::TYPE_MAP[$type] ?? null;
        if ($primixType === null) {
            return null;
        }

        $def = ['type' => $primixType];
        $label = trim((string) ($props['label'] ?? ''));

        // Tabs: children are tab nodes → { label, name, schema }.
        if ($type === 'tabs-layout') {
            if ($label !== '') {
                $def['label'] = $label;
            }
            $def['tabs'] = $this->buildBranches($id, 'tab');

            return $def;
        }

        // Wizard: children are wizard-step nodes → { label, name, schema }.
        if ($type === 'wizard') {
            if ($label !== '') {
                $def['label'] = $label;
            }
            $def['steps'] = $this->buildBranches($id, 'step');

            return $def;
        }

        // Layout containers (grid/group/section/fieldset): nested schema.
        if (in_array($type, ['grid', 'group', 'section', 'fieldset'], true)) {
            if ($label !== '') {
                $def['label'] = $label;
            }
            $columns = (int) ($props['columns'] ?? 0);
            if ($columns > 0) {
                $def['columns'] = $columns;
            }
            $this->applySpan($def, $props);
            $def['schema'] = $this->buildLevel($id);

            return $def;
        }

        // Leaf field.
        $def['name'] = $this->fieldName($props, $id);
        if ($label !== '') {
            $def['label'] = $label;
        }
        $placeholder = trim((string) ($props['placeholder'] ?? ''));
        if ($placeholder !== '') {
            $def['placeholder'] = $placeholder;
        }
        $helper = trim((string) ($props['helper_text'] ?? ''));
        if ($helper !== '') {
            $def['helperText'] = $helper;
        }
        $default = $props['default_value'] ?? null;
        if ($default !== null && $default !== '') {
            $def['default'] = $default;
        }
        if ((bool) data_get($props, 'validation.required', false)) {
            $def['required'] = true;
        }
        if (in_array($type, ['select', 'radio'], true)) {
            $options = $this->options($props['options'] ?? null);
            if ($options !== []) {
                $def['options'] = $options;
            }
        }
        $this->applySpan($def, $props);

        return $def;
    }

    /**
     * Build the { label, name, schema } branch list for tabs/wizard children.
     *
     * @param  string  $namePrefix  'tab' | 'step'
     * @return array<int, array<string,mixed>>
     */
    private function buildBranches(string $parentId, string $namePrefix): array
    {
        $branches = [];
        foreach ($this->childrenByParent[$parentId] ?? [] as $child) {
            $branches[] = $this->branch($child, $namePrefix);
        }

        return $branches;
    }

    /**
     * A single tab/step branch. A UNIQUE name is mandatory: Primix keys tabs/steps
     * by name (derived from the label slug by default), so unlabelled tabs would
     * all collapse onto the same name and behave like one tab. We force a unique
     * name from the node id.
     *
     * @return array<string,mixed>
     */
    private function branch(array $node, string $namePrefix): array
    {
        $props = $this->props($node);
        $label = trim((string) ($props['label'] ?? ''));
        $id = (string) ($node['id'] ?? '');

        return [
            'label' => $label !== '' ? $label : ($namePrefix === 'tab' ? 'Tab' : 'Step'),
            'name' => $namePrefix . '_' . substr(md5($id !== '' ? $id : uniqid($namePrefix, true)), 0, 10),
            'schema' => $this->buildLevel($id),
        ];
    }

    private function applySpan(array &$def, array $props): void
    {
        $span = (int) ($props['column_span'] ?? 0);
        if ($span > 0) {
            $def['columnSpan'] = $span;
        }
    }

    /** Merge a node's root + content props (content wins). */
    private function props(array $node): array
    {
        $raw = is_array($node['props'] ?? null) ? $node['props'] : [];
        $content = is_array($raw['content'] ?? null) ? $raw['content'] : [];
        $merged = array_merge($raw, $content);
        // Keep validation reachable via data_get($props, 'validation.required').
        if (! isset($merged['validation']) && isset($raw['validation'])) {
            $merged['validation'] = $raw['validation'];
        }

        return $merged;
    }

    private function fieldName(array $props, string $id): string
    {
        $name = trim((string) ($props['name'] ?? ''));
        if ($name !== '') {
            return Str::slug($name, '_');
        }

        return 'field_' . substr(md5($id !== '' ? $id : uniqid('f', true)), 0, 8);
    }

    /**
     * Tagixo options ([{label,value}]) → Primix options (['value' => 'label']).
     *
     * @return array<string,string>
     */
    private function options(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }
        $out = [];
        foreach ($options as $opt) {
            if (! is_array($opt)) {
                continue;
            }
            $value = (string) ($opt['value'] ?? '');
            if ($value === '') {
                continue;
            }
            $out[$value] = (string) ($opt['label'] ?? $value);
        }

        return $out;
    }
}
