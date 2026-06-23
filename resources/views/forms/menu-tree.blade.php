@php
    use Illuminate\Support\Js;

    // value => label map for the link-type chip shown on each row.
    $linkTypeLabels = collect($linkTypeOptions ?? [])->pluck('label', 'value');
@endphp

<div class="menu-tree" data-menu-tree>
    {{-- Tree --}}
    <div class="menu-tree-list flex flex-col" data-menu-tree-list>
        <template v-for="(item, i) in {{ $statePath }}" :key="i">
            <div
                class="menu-tree-row group flex items-center gap-2 py-2 pe-2 ps-3 mb-1 rounded-md bg-[var(--p-content-background)] ring-1 ring-[var(--p-content-border-color)]"
                :data-index="i"
                :data-depth="item.depth || 0"
                :style="{ marginInlineStart: ((item.depth || 0) * 1.75) + 'rem' }"
            >
                <span
                    class="menu-tree-handle cursor-move text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 shrink-0"
                    title="{{ __('Drag to reorder — drag right/left to change level') }}"
                >
                    <i class="pi pi-bars"></i>
                </span>

                <span class="flex-1 min-w-0 truncate text-sm font-medium">
                    <span v-if="item.label" v-text="item.label"></span>
                    <span v-else class="italic text-surface-400">{{ __('Untitled item') }}</span>
                </span>

                <p-tag
                    :value="({!! Js::from($linkTypeLabels) !!}[item.target_type] || item.target_type)"
                    severity="secondary"
                    class="shrink-0 hidden sm:inline-flex"
                ></p-tag>

                <p-toggle-switch
                    v-model="item.visible"
                    class="shrink-0"
                    v-tooltip.top="'{{ __('Visible') }}'"
                ></p-toggle-switch>

                <div class="menu-tree-actions flex items-center gap-0.5 shrink-0">
                    <p-button
                        icon="pi pi-angle-left" text rounded size="small" severity="secondary"
                        v-tooltip.top="'{{ __('Outdent') }}'"
                        @click="menuTreeOutdent(i)"
                    ></p-button>
                    <p-button
                        icon="pi pi-angle-right" text rounded size="small" severity="secondary"
                        v-tooltip.top="'{{ __('Indent') }}'"
                        @click="menuTreeIndent(i)"
                    ></p-button>
                    <p-button
                        icon="pi pi-arrow-up" text rounded size="small" severity="secondary"
                        v-tooltip.top="'{{ __('Move up') }}'"
                        @click="menuTreeMoveUp(i)"
                    ></p-button>
                    <p-button
                        icon="pi pi-arrow-down" text rounded size="small" severity="secondary"
                        v-tooltip.top="'{{ __('Move down') }}'"
                        @click="menuTreeMoveDown(i)"
                    ></p-button>
                    <p-button
                        icon="pi pi-pencil" text rounded size="small"
                        v-tooltip.top="'{{ __('Edit') }}'"
                        @click="menuTreeOpenEdit(i)"
                    ></p-button>
                    <p-button
                        icon="pi pi-trash" text rounded size="small" severity="danger"
                        v-tooltip.top="'{{ __('Delete') }}'"
                        @click="menuTreeRemove(i)"
                    ></p-button>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div
        v-if="!{{ $statePath }} || {{ $statePath }}.length === 0"
        class="text-center text-sm text-surface-400 py-6 rounded-md border border-dashed border-[var(--p-content-border-color)]"
    >
        {{ __('No menu items yet. Add the first one to get started.') }}
    </div>

    {{-- Add --}}
    <div class="mt-3">
        <p-button
            icon="pi pi-plus"
            label="{{ __('Add item') }}"
            severity="secondary"
            outlined
            size="small"
            @click="menuTreeAdd()"
        ></p-button>
    </div>

    {{-- Edit modal — bound to the editingItem working copy --}}
    <p-dialog
        :visible="editingItemIndex !== null"
        @update:visible="(v) => { if (!v) menuTreeCloseEdit() }"
        modal
        :dismissable-mask="true"
        header="{{ __('Edit menu item') }}"
        :style="{ width: '32rem' }"
        :breakpoints="{ '640px': '95vw' }"
    >
        <div class="flex flex-col gap-4 pt-2" v-if="editingItemIndex !== null">
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Label') }}</label>
                <p-input-text v-model="editingItem.label" fluid autofocus></p-input-text>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Link type') }}</label>
                <p-select
                    v-model="editingItem.target_type"
                    :options="{!! Js::from($linkTypeOptions ?? []) !!}"
                    option-label="label"
                    option-value="value"
                    fluid
                ></p-select>
            </div>

            <div class="flex flex-col gap-1" v-if="editingItem.target_type === 'page'">
                <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Page') }}</label>
                <p-select
                    v-model="editingItem.target_page_id"
                    :options="{!! Js::from($pageOptions ?? []) !!}"
                    option-label="label"
                    option-value="value"
                    filter
                    show-clear
                    fluid
                ></p-select>
                <small class="text-surface-400">{{ __('Pick the destination page.') }}</small>
            </div>

            <div class="flex flex-col gap-1" v-else>
                <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Link target') }}</label>
                <p-input-text v-model="editingItem.target_value" fluid></p-input-text>
                <small class="text-surface-400">{{ __('URL, route name, or anchor (#section). Depends on the link type.') }}</small>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Icon') }}</label>
                    <p-input-text v-model="editingItem.icon" placeholder="heroicon-o-home" fluid></p-input-text>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Item CSS class') }}</label>
                    <p-input-text v-model="editingItem.css_class" fluid></p-input-text>
                </div>
            </div>

            <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200">
                    <p-toggle-switch v-model="editingItem.new_tab"></p-toggle-switch>
                    {{ __('Open in new tab') }}
                </label>
                <label class="flex items-center gap-2 text-sm text-surface-700 dark:text-surface-200">
                    <p-toggle-switch v-model="editingItem.visible"></p-toggle-switch>
                    {{ __('Visible') }}
                </label>
            </div>
        </div>

        <template #footer>
            <p-button label="{{ __('Cancel') }}" text severity="secondary" @click="menuTreeCloseEdit()"></p-button>
            <p-button label="{{ __('Save') }}" icon="pi pi-check" @click="menuTreeSaveEdit()"></p-button>
        </template>
    </p-dialog>
</div>

@script
{{-- Defines window.Sortable once (tagixo-primix ships no JS build). --}}
@include('tagixo-primix::forms._sortable')

// Drag = reorder; horizontal drop position = depth. The whole subtree of the
// dragged item moves with it. The client sends only {index, depth} pairs to
// the server (menuTreeReorder), which rebuilds the list from existing items so
// no field data is lost. The server then re-normalizes depths.
const MENU_TREE_INDENT_STEP = 28; // px per level (≈ 1.75rem on the rows)

const menuTreeSnapshot = (list) =>
    Array.from(list.querySelectorAll('.menu-tree-row')).map((row) => ({
        index: Number(row.dataset.index),
        depth: Number(row.dataset.depth) || 0,
    }));

const initMenuTreeSortable = () => {
    const list = document.querySelector('[data-menu-tree-list]');

    if (!list || !window.Sortable || list.__menuTreeSortable) {
        return;
    }

    let snapshot = [];
    let dragPos = 0;
    let dragLen = 1;

    list.__menuTreeSortable = window.Sortable.create(list, {
        handle: '.menu-tree-handle',
        animation: 150,
        onStart: (evt) => {
            snapshot = menuTreeSnapshot(list);
            dragPos = evt.oldIndex;
            const depth = snapshot[dragPos] ? snapshot[dragPos].depth : 0;
            dragLen = 1;
            for (let j = dragPos + 1; j < snapshot.length; j++) {
                if (snapshot[j].depth > depth) dragLen++;
                else break;
            }
        },
        onEnd: (evt) => {
            const bridge = (typeof livue !== 'undefined' && livue) || window.livue;
            if (!bridge || !bridge.call) return;

            const block = snapshot.slice(dragPos, dragPos + dragLen);
            const rest = snapshot.slice(0, dragPos).concat(snapshot.slice(dragPos + dragLen));

            let target = evt.newIndex;
            if (evt.newIndex > evt.oldIndex) target = evt.newIndex - dragLen + 1;
            target = Math.max(0, Math.min(target, rest.length));

            const rect = list.getBoundingClientRect();
            const oe = evt.originalEvent || {};
            const touch = oe.changedTouches && oe.changedTouches[0];
            const clientX = (oe.clientX != null ? oe.clientX : (touch ? touch.clientX : rect.left));

            let desired = Math.round((clientX - rect.left) / MENU_TREE_INDENT_STEP);
            const maxDepth = target > 0 ? rest[target - 1].depth + 1 : 0;
            desired = Math.max(0, Math.min(desired, maxDepth));

            const delta = desired - (block[0] ? block[0].depth : 0);
            block.forEach((b) => { b.depth = Math.max(0, b.depth + delta); });

            const order = rest
                .slice(0, target)
                .concat(block, rest.slice(target))
                .map((o) => ({ index: o.index, depth: o.depth }));

            bridge.call('menuTreeReorder', [order]);
        },
    });
};

onMounted(() => { initMenuTreeSortable(); });
document.addEventListener('livue:navigated', initMenuTreeSortable);

return {};
@endscript
