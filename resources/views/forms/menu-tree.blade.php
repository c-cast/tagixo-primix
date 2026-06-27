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
                class="menu-tree-row group flex items-center gap-2 mb-1 rounded-md bg-[var(--p-content-background)] ring-1 ring-[var(--p-content-border-color)]"
                :data-index="i"
                :data-depth="item.depth || 0"
                {{-- Paddings inline (not Tailwind classes): the prebuilt Primix admin CSS
                     bundle doesn't include every spacing utility, so ps-4/py-2.5 silently
                     no-op. Inline styles are bundle-independent. --}}
                :style="{
                    marginInlineStart: ((item.depth || 0) * 1.75) + 'rem',
                    paddingInlineStart: '1rem',
                    paddingInlineEnd: '0.5rem',
                    paddingTop: '0.5rem',
                    paddingBottom: '0.5rem',
                }"
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

// Resolve the LiVue component that OWNS the menu tree, so server method calls
// land on the right page component. The `livue` injected into this @script and
// `window.livue` resolve to the wrong instance here (e.g. primix-topbar), so we
// match the list's nearest [data-livue-id] ancestor against LiVue.all().
const menuTreeBridge = (list) => {
    const rootEl = list && list.closest('[data-livue-id]');
    const rootId = rootEl && rootEl.getAttribute('data-livue-id');
    const api = window.LiVue;

    if (rootId && api && typeof api.all === 'function') {
        const all = api.all();
        const arr = Array.isArray(all) ? all : Object.values(all || {});
        const comp = arr.find((c) => c && (
            c.componentId === rootId ||
            (c.el && c.el.getAttribute && c.el.getAttribute('data-livue-id') === rootId)
        ));
        if (comp) {
            return (comp._rootLivue && comp._rootLivue.call) ? comp._rootLivue : comp;
        }
    }

    if (typeof livue !== 'undefined' && livue && livue.call) return livue;
    return window.livue;
};

const initMenuTreeSortable = () => {
    const list = document.querySelector('[data-menu-tree-list]');

    if (!list || !window.Sortable || list.__menuTreeSortable) {
        return;
    }

    let snapshot = [];
    let domOrder = [];
    let dragPos = 0;
    let dragLen = 1;
    let dragItem = null;
    let startX = null;
    let startY = null;
    let currentX = null;
    let currentY = null;
    // 'horizontal' = depth-change gesture (lock vertical pos); 'vertical' = reorder; null = undecided.
    let gestureType = null;
    let startDepth = 0;
    let candidateDepth = 0;

    // Max depth allowed at the dragged row's CURRENT drop position: one deeper
    // than the row immediately above it (you can be its child or any shallower
    // sibling); 0 at the very top.
    const maxDepthAtDragPosition = () => {
        const rows = Array.from(list.querySelectorAll('.menu-tree-row'));
        const idx = rows.indexOf(dragItem);
        const prev = idx > 0 ? rows[idx - 1] : null;
        return prev ? (Number(prev.dataset.depth) || 0) + 1 : 0;
    };

    // Live horizontal tracking: drag right/left to pick the target depth (WP
    // style). The dragged row indents in real time as a ghost of where/at what
    // level it will land — same level vs sub-item. Also detects whether the
    // gesture is primarily horizontal (depth change) or vertical (reorder).
    const onMenuTreeDragMove = (e) => {
        if (!dragItem) return;
        const x = e.clientX != null ? e.clientX
            : (e.touches && e.touches[0] ? e.touches[0].clientX : null);
        const y = e.clientY != null ? e.clientY
            : (e.touches && e.touches[0] ? e.touches[0].clientY : null);
        if (x == null) return;
        if (startX === null) { startX = x; startY = y; }
        currentX = x;
        currentY = y;

        // Latch gesture type on the first significant movement (>10 px on either axis).
        if (!gestureType && startY != null && y != null) {
            const dx = Math.abs(x - startX);
            const dy = Math.abs(y - startY);
            if (dx > 10 || dy > 10) {
                gestureType = dx > dy ? 'horizontal' : 'vertical';
            }
        }

        const maxDepth = maxDepthAtDragPosition();
        let d = startDepth + Math.round((x - startX) / MENU_TREE_INDENT_STEP);
        d = Math.max(0, Math.min(d, maxDepth));
        candidateDepth = d;

        dragItem.style.marginInlineStart = (d * 1.75) + 'rem';
    };

    const MENU_TREE_MOVE_EVENTS = ['dragover', 'pointermove', 'touchmove'];

    list.__menuTreeSortable = window.Sortable.create(list, {
        handle: '.menu-tree-handle',
        animation: 150,
        dataIdAttr: 'data-index',
        ghostClass: 'menu-tree-ghost',
        onStart: (evt) => {
            // Option callbacks get SortableJS's own event (evt.target is the
            // dragged item, not the list), so grab the instance from the closure.
            const instance = list.__menuTreeSortable || window.Sortable.get(list);
            snapshot = menuTreeSnapshot(list);
            domOrder = instance ? instance.toArray() : [];
            dragPos = evt.oldIndex;
            startDepth = snapshot[dragPos] ? snapshot[dragPos].depth : 0;
            candidateDepth = startDepth;
            dragItem = evt.item;
            startX = null;
            startY = null;
            currentX = null;
            currentY = null;
            gestureType = null;

            dragLen = 1;
            for (let j = dragPos + 1; j < snapshot.length; j++) {
                if (snapshot[j].depth > startDepth) dragLen++;
                else break;
            }

            // Make the dragged row read as a drop-preview ghost.
            dragItem.style.outline = '2px dashed var(--p-primary-color, #6366f1)';
            dragItem.style.outlineOffset = '-2px';
            dragItem.style.opacity = '0.85';

            MENU_TREE_MOVE_EVENTS.forEach((t) => document.addEventListener(t, onMenuTreeDragMove, true));
        },
        // When horizontal movement dominates (depth-change gesture), prevent
        // SortableJS from reordering vertically so the item stays in place.
        onMove: () => gestureType !== 'horizontal',
        onEnd: (evt) => {
            MENU_TREE_MOVE_EVENTS.forEach((t) => document.removeEventListener(t, onMenuTreeDragMove, true));

            if (dragItem) {
                dragItem.style.marginInlineStart = '';
                dragItem.style.outline = '';
                dragItem.style.outlineOffset = '';
                dragItem.style.opacity = '';
            }

            const instance = list.__menuTreeSortable || window.Sortable.get(list);

            // For a horizontal (depth-change) gesture the item didn't move vertically,
            // so use the pre-drag order. For a vertical (reorder) gesture, read the
            // new order from the DOM after SortableJS moved the element.
            const newOrder = (gestureType === 'horizontal' || !instance)
                ? domOrder.map(Number)
                : instance.toArray().map(Number);

            // Revert the DOM so Vue's v-for vdom stays consistent; the server
            // reorder + re-render is the source of truth.
            if (instance && domOrder.length) {
                instance.sort(domOrder, false);
            }

            const bridge = menuTreeBridge(list);
            if (!bridge || !bridge.call) { dragItem = null; return; }

            // The dragged block = the item plus its descendants (contiguous in the
            // original snapshot). Walk the new sequence and reassemble: at the
            // dragged row's new spot splice the whole block in, skip its children
            // wherever SortableJS left them. This keeps subtrees intact for any
            // depth / item count (no fragile newIndex math).
            const block = snapshot.slice(dragPos, dragPos + dragLen);
            const inBlock = new Set();
            for (let k = 0; k < dragLen; k++) inBlock.add(dragPos + k);

            const result = [];
            for (const pos of newOrder) {
                if (pos === dragPos) {
                    for (const b of block) result.push(b);
                } else if (! inBlock.has(pos)) {
                    result.push(snapshot[pos]);
                }
            }

            // Clamp the depth the user picked horizontally to what's valid at the
            // final position (at most one deeper than the row above the block).
            const at = result.indexOf(block[0]);
            const above = at > 0 ? result[at - 1] : null;
            const maxDepth = above ? above.depth + 1 : 0;
            const finalDepth = Math.max(0, Math.min(candidateDepth, maxDepth));

            const delta = finalDepth - (block[0] ? block[0].depth : 0);
            block.forEach((b) => { b.depth = Math.max(0, b.depth + delta); });

            dragItem = null;
            bridge.call('menuTreeReorder', [result.map((o) => ({ index: o.index, depth: o.depth }))]);
        },
    });
};

// NB: LiVue's @script runs in setup() but `onMounted` does not reliably fire
// here, and LiVue re-renders can replace the list element (dropping the binding),
// so a persistent watcher (re)binds whenever the current list lacks a Sortable.
const ensureMenuTreeSortable = () => {
    const list = document.querySelector('[data-menu-tree-list]');
    if (list && window.Sortable && !list.__menuTreeSortable) {
        initMenuTreeSortable();
    }
};
ensureMenuTreeSortable();
if (!window.__menuTreeWatcher) {
    window.__menuTreeWatcher = setInterval(ensureMenuTreeSortable, 50);
}
document.addEventListener('livue:navigated', ensureMenuTreeSortable);

return {};
@endscript
