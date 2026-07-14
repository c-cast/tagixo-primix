@php
    use Illuminate\Support\Js;

    // value => label map for the link-type chip shown on each row.
    $linkTypeLabels = collect($linkTypeOptions ?? [])->pluck('label', 'value');
@endphp

<div class="menu-tree" data-menu-tree>
    {{-- Tree --}}
    <div class="menu-tree-list flex flex-col" data-menu-tree-list>
        <template v-for="(item, i) in {{ $statePath }}" :key="item._key || i">
            <div
                v-show="!isHidden({{ $statePath }}, i)"
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
                <button
                    v-if="hasChildren({{ $statePath }}, i)"
                    type="button"
                    @click="toggleCollapse({{ $statePath }}, item, i)"
                    :title="collapsedKeys.has(itemKey(item, i)) ? '{{ __('Expand') }}' : '{{ __('Collapse') }}'"
                    style="background:none;border:none;padding:2px;cursor:pointer;color:var(--p-text-muted-color);line-height:1;flex-shrink:0;"
                >
                    <i :class="collapsedKeys.has(itemKey(item, i)) ? 'pi pi-chevron-right' : 'pi pi-chevron-down'" style="font-size:0.65rem;"></i>
                </button>
                <span v-else style="width:1rem;flex-shrink:0;display:inline-block;"></span>

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
                    v-if="item.dropdown_type === 'mega'"
                    value="{{ __('Mega menu') }}"
                    severity="warn"
                    class="shrink-0 hidden sm:inline-flex"
                ></p-tag>

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

            <div class="flex flex-col gap-1" v-if="editingItem.depth === 0">
                <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Dropdown type') }}</label>
                <p-select
                    v-model="editingItem.dropdown_type"
                    :options="{!! Js::from($dropdownTypeOptions ?? []) !!}"
                    option-label="label"
                    option-value="value"
                    fluid
                ></p-select>
                <small class="text-surface-400">{{ __('Choose "Mega menu" to render this item\'s children as a wide panel with columns.') }}</small>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ __('Icon') }}</label>
                <div class="flex items-center gap-2">
                    <span
                        v-if="editingItem.icon && iconSvgCache[editingItem.icon]"
                        v-html="iconSvgCache[editingItem.icon]"
                        class="w-5 h-5 shrink-0 [&_svg]:w-full [&_svg]:h-full"
                    ></span>
                    <p-input-text
                        :value="editingItem.icon || ''"
                        readonly
                        :placeholder="'{{ __('No icon') }}'"
                        fluid
                        class="flex-1 cursor-pointer"
                        @click="iconPickerRef && iconPickerRef.toggle($event)"
                    ></p-input-text>
                    <p-button
                        icon="pi pi-th-large"
                        text rounded
                        v-tooltip.top="'{{ __('Browse icons') }}'"
                        @click="iconPickerRef && iconPickerRef.toggle($event)"
                    ></p-button>
                    <p-button
                        v-if="editingItem.icon"
                        icon="pi pi-times"
                        text rounded
                        severity="danger"
                        v-tooltip.top="'{{ __('Remove icon') }}'"
                        @click="iconClear()"
                    ></p-button>
                </div>

                <p-popover ref="iconPickerRef" :style="{ width: '420px' }">
                    <div class="flex flex-col gap-3 p-1">
                        <p-select
                            v-if="iconSetsData.length > 1"
                            v-model="iconActiveSet"
                            :options="iconSetsData"
                            option-label="label"
                            option-value="name"
                            fluid
                            size="small"
                        ></p-select>
                        <p-input-text
                            v-model="iconSearch"
                            :placeholder="'{{ __('Search icons...') }}'"
                            fluid
                            size="small"
                        ></p-input-text>
                        <div ref="iconGridRef" style="display:flex;flex-wrap:wrap;gap:0.25rem;max-height:16rem;overflow-y:auto">
                            <button
                                v-for="iconId in iconVisibleIcons"
                                :key="iconId"
                                type="button"
                                @click="iconSelect(iconId)"
                                :title="iconId"
                                :style="{
                                    width: '2rem', height: '2rem',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    borderRadius: '0.375rem', border: 'none', cursor: 'pointer',
                                    background: editingItem.icon === iconId ? 'var(--p-primary-100, #e0e7ff)' : 'transparent',
                                    outline: editingItem.icon === iconId ? '1px solid var(--p-primary-500, #6366f1)' : 'none',
                                }"
                                @mouseenter="$event.currentTarget.style.background = editingItem.icon === iconId ? 'var(--p-primary-100, #e0e7ff)' : 'var(--p-surface-100, #f1f5f9)'"
                                @mouseleave="$event.currentTarget.style.background = editingItem.icon === iconId ? 'var(--p-primary-100, #e0e7ff)' : 'transparent'"
                            >
                                <span
                                    v-if="iconSvgCache[iconId]"
                                    v-html="iconSvgCache[iconId]"
                                    style="width:1.25rem;height:1.25rem;pointer-events:none;display:flex"
                                ></span>
                                <span
                                    v-else
                                    style="width:1.25rem;height:1.25rem;border-radius:0.25rem;background:var(--p-surface-200,#e2e8f0)"
                                ></span>
                            </button>
                            <div ref="iconSentinelRef" style="width:100%;height:1px"></div>
                            <p
                                v-if="iconFilteredIcons.length === 0"
                                style="width:100%;text-align:center;padding:1rem 0;font-size:0.875rem;color:var(--p-surface-400)"
                            >{{ __('No icons found') }}</p>
                        </div>
                    </div>
                </p-popover>
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
    let dragPos = 0;
    let dragLen = 1;
    let dragItem = null;
    let startX = null;
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
    // level it will land — same level vs sub-item.
    const onMenuTreeDragMove = (e) => {
        if (!dragItem) return;
        const x = e.clientX != null ? e.clientX
            : (e.touches && e.touches[0] ? e.touches[0].clientX : null);
        if (x == null) return;
        if (startX === null) startX = x;

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
            dragPos = evt.oldIndex;
            startDepth = snapshot[dragPos] ? snapshot[dragPos].depth : 0;
            candidateDepth = startDepth;
            dragItem = evt.item;
            startX = null;

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
        onEnd: (evt) => {
            MENU_TREE_MOVE_EVENTS.forEach((t) => document.removeEventListener(t, onMenuTreeDragMove, true));

            if (dragItem) {
                dragItem.style.marginInlineStart = '';
                dragItem.style.outline = '';
                dragItem.style.outlineOffset = '';
                dragItem.style.opacity = '';
            }

            const instance = list.__menuTreeSortable || window.Sortable.get(list);

            // Read the new data-index order SortableJS produced. newOrder contains
            // data-index VALUES (from dataIdAttr:'data-index'), not DOM positions.
            // We do NOT revert the DOM: SortableJS only moves element nodes, never
            // Vue's fragment comment anchors, so the anchor stays at the end and
            // subsequent Vue insertions land correctly. With stable :key="_key",
            // Vue moves elements to the right positions after the server responds.
            const newOrder = instance ? instance.toArray().map(Number) : [];

            const bridge = menuTreeBridge(list);
            if (!bridge || !bridge.call) { dragItem = null; return; }

            // The dragged block = dragged item + its descendants in the snapshot.
            const block = snapshot.slice(dragPos, dragPos + dragLen);

            // dragDataIndex: the data-index value of the dragged element.
            // blockIndices: data-index values of all block members (to skip them
            // when they appear elsewhere in newOrder as SortableJS scattered them).
            const dragDataIndex = block[0] ? block[0].index : -1;
            const blockIndices = new Set(block.map((b) => b.index));

            // O(1) lookup: data-index → snapshot entry.
            const snapByIdx = {};
            snapshot.forEach((s) => { snapByIdx[s.index] = s; });

            // Walk the new data-index sequence SortableJS produced. At the dragged
            // item's new position inject the whole block; ignore scattered block
            // members wherever SortableJS left them.
            const result = [];
            for (const dataIdx of newOrder) {
                if (dataIdx === dragDataIndex) {
                    for (const b of block) result.push(b);
                } else if (!blockIndices.has(dataIdx)) {
                    result.push(snapByIdx[dataIdx]);
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
            const payload = result.map((o) => ({ index: o.index, depth: o.depth }));
            bridge.call('menuTreeReorder', [payload]);
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

// ── Icon picker ──────────────────────────────────────────────────────────────
const ICON_PAGE = 60
const iconSetsData = {!! Js::from($iconSets ?? []) !!}
const iconPickerRef = ref(null)
const iconGridRef = ref(null)
const iconSentinelRef = ref(null)
const iconSearch = ref('')
const iconActiveSet = ref(iconSetsData[0]?.name ?? null)
const iconSvgCache = ref({})
const iconVisibleCount = ref(ICON_PAGE)

// All matching IDs (no cap)
const iconFilteredIcons = computed(() => {
    const set = iconSetsData.find(s => s.name === iconActiveSet.value)
    if (!set) return []
    const q = iconSearch.value.trim().toLowerCase()
    const ids = []
    const variants = set.variants || []
    if (variants.length > 0) {
        const variant = variants[0]
        for (const name of (set.icons || [])) {
            if (!q || name.includes(q)) ids.push(set.name + '/' + variant.prefix + name)
        }
    } else {
        for (const name of (set.icons || [])) {
            if (!q || name.includes(q)) ids.push(set.name + '/' + name)
        }
    }
    return ids
})

// Slice shown to user — grows as they scroll
const iconVisibleIcons = computed(() =>
    iconFilteredIcons.value.slice(0, iconVisibleCount.value)
)

// Reset page when filter changes
watch([iconFilteredIcons], () => { iconVisibleCount.value = ICON_PAGE })

const iconFetchBatch = async (ids) => {
    const missing = ids.filter(id => !iconSvgCache.value[id])
    if (!missing.length) return
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? ''
    try {
        const res = await fetch('/tagixo/icons/batch', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ids: missing }),
        })
        if (!res.ok) return
        const data = await res.json()
        iconSvgCache.value = { ...iconSvgCache.value, ...data }
    } catch {}
}

// Fetch SVGs whenever visible slice changes
watch(iconVisibleIcons, (ids) => { iconFetchBatch(ids) }, { immediate: true })

// Fetch SVG for currently selected icon
watch(() => editingItem.value?.icon, (id) => {
    if (id && !iconSvgCache.value[id]) iconFetchBatch([id])
}, { immediate: true })

// IntersectionObserver on sentinel: load next page when bottom is reached
let iconObserver = null
const iconSetupObserver = () => {
    if (iconObserver) iconObserver.disconnect()
    const grid = iconGridRef.value?.$el ?? iconGridRef.value
    if (!grid) return
    iconObserver = new IntersectionObserver((entries) => {
        if (entries[0]?.isIntersecting && iconVisibleCount.value < iconFilteredIcons.value.length) {
            iconVisibleCount.value += ICON_PAGE
        }
    }, { root: grid, threshold: 0.1 })
    const sentinel = iconSentinelRef.value?.$el ?? iconSentinelRef.value
    if (sentinel) iconObserver.observe(sentinel)
}
watch([iconGridRef, iconSentinelRef], iconSetupObserver)

const iconSelect = (iconId) => {
    editingItem.value.icon = iconId
    iconPickerRef.value?.hide()
}

const iconClear = () => { editingItem.value.icon = null }

// ── Collapse/expand ───────────────────────────────────────────────────────────
const collapsedKeys = ref(new Set())

const itemKey = (item, i) => String(item._key || i)

const hasChildren = (arr, i) => {
    const depth = arr[i]?.depth ?? 0
    return (arr[i + 1]?.depth ?? -1) > depth
}

const toggleCollapse = (arr, item, i) => {
    const k = itemKey(item, i)
    const s = new Set(collapsedKeys.value)
    if (s.has(k)) s.delete(k); else s.add(k)
    collapsedKeys.value = s
}

const isHidden = (arr, i) => {
    const iDepth = arr[i]?.depth ?? 0
    for (let j = i - 1; j >= 0; j--) {
        const jDepth = arr[j]?.depth ?? 0
        if (jDepth < iDepth) {
            if (collapsedKeys.value.has(itemKey(arr[j], j))) return true
        } else {
            break
        }
    }
    return false
}

return { iconPickerRef, iconGridRef, iconSentinelRef, iconSearch, iconActiveSet, iconSvgCache, iconFilteredIcons, iconVisibleIcons, iconSelect, iconClear, iconSetsData, collapsedKeys, hasChildren, toggleCollapse, isHidden, itemKey };
@endscript
