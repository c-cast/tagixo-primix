@php
    $tagixoAssetVersion = static function (string $relative): string {
        $path = public_path('vendor/tagixo/' . $relative);

        return is_file($path) ? (string) filemtime($path) : (string) time();
    };
@endphp
<link rel="stylesheet" href="{{ asset('vendor/tagixo/tagixo.css') }}?v={{ $tagixoAssetVersion('tagixo.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/tagixo/builder-vendor.css') }}?v={{ $tagixoAssetVersion('builder-vendor.css') }}">
{{-- Resolved through the build manifest to the content-hashed entry
     (builder-<hash>.js). The hash IS the cache-bust, so no ?v= query is needed
     — and because the lazy chunks import the same hashed entry, builder.js is
     still evaluated exactly once (no duplicate Pinia / "reading '_s'" crash). --}}
<script type="module" src="{{ \Ccast\Tagixo\Support\Assets::url('builder.js') }}"></script>

@push('styles')
    <style id="tagixo-dynamic-styles">{!! $this->getInitialStylesheet() !!}</style>
@endpush

@php
    $layoutFramePayload = method_exists($this, 'getLayoutFrameForVue') ? $this->getLayoutFrameForVue() : null;
@endphp

<script id="tagixo-layout-frame" type="application/json">@json($layoutFramePayload)</script>

<div class="tagixo-container h-screen flex flex-col bg-gray-100 dark:bg-gray-800">
    <div
        id="tagixo-vue"
        class="flex-1 w-full min-h-0"
        data-structure="{{ json_encode($this->getStructureForVue()) }}"
        data-body-props="{{ json_encode($bodyProps ?? []) }}"
        data-available-components="{{ json_encode($this->getAvailableComponentsForVue()) }}"
        data-context="{{ $context }}"
        @if ($recordKey = ($this->record ?? null)?->getKey())
            data-record-id="{{ $recordKey }}"
        @endif
        data-global-variables="{{ json_encode($this->getGlobalVariablesForVue()) }}"
        data-page-attributes="{{ json_encode($this->getPageAttributesForVue()) }}"
        data-translations="{{ json_encode($this->getTranslationsForVue()) }}"
        data-available-icons="{{ json_encode($this->getAvailableIconsForVue()) }}"
        data-available-fonts="{{ json_encode($this->getAvailableFontsForVue()) }}"
        data-prop-type-registry="{{ json_encode($this->getPropTypeRegistryForVue()) }}"
        data-canvas="{{ json_encode($this->getCanvasForVue()) }}"
        @if ($previewUrl = $this->getPreviewUrl())
            data-preview-url="{{ $previewUrl }}"
        @endif
        @if ($backUrl = $this->getBackUrl())
            data-back-url="{{ $backUrl }}"
        @endif
    >
        <div class="h-full flex items-center justify-center">
            <div class="text-center">
                <svg class="animate-spin h-10 w-10 text-primary-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">{{ __('Loading Visual Builder...') }}</p>
            </div>
        </div>
    </div>
</div>

@script
<script>
    function resolveBuilderLivueBridge() {
        if (window.livue?.call) {
            return window.livue
        }

        const firstComponent = window.LiVue?.first?.()

        if (firstComponent?._rootLivue?.call) {
            return firstComponent._rootLivue
        }

        if (firstComponent?.call) {
            return firstComponent
        }

        return null
    }

    async function refreshBuilderStyles(structure = null) {
        try {
            const mountNode = document.getElementById('tagixo-vue')
            const fallbackStructure = mountNode?.dataset?.structure
                ? JSON.parse(mountNode.dataset.structure)
                : { body: {}, components: [] }
            const context = mountNode?.dataset?.context || 'page'
            const nextStructure = structure || fallbackStructure
            let css = ''
            const livue = resolveBuilderLivueBridge()

            if (livue?.call) {
                css = await livue.call('regenerateStylesheet', [nextStructure]) || ''
            }

            if (!css) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                const response = await fetch(@json(url('/tagixo/builder/stylesheet')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    body: JSON.stringify({
                        structure: nextStructure,
                        context,
                    }),
                })
                const result = await response.json()
                css = result?.css || ''
            }

            const styleEl = document.getElementById('tagixo-dynamic-styles')
            if (styleEl) {
                styleEl.textContent = css
                document.head.appendChild(styleEl)
            }
            const optimisticStyleEl = document.getElementById('vb-dynamic-styles')
            if (optimisticStyleEl) optimisticStyleEl.remove()
        } catch (error) {
            console.error('[VisualBuilder] Failed to update stylesheet:', error)
        }
    }

    window.addEventListener('tagixo:save', async (e) => {
        const livue = resolveBuilderLivueBridge()

        if (! livue?.call) {
            window.dispatchEvent(new CustomEvent('tagixo:save-error', {
                detail: { message: '{{ __("Builder bridge unavailable") }}' }
            }))
            return
        }

        await livue.call('saveFromVue', [e.detail.structure]);
    });

    window.addEventListener('tagixo:save-global-variables', async (e) => {
        const livue = resolveBuilderLivueBridge()

        if (! livue?.call) {
            window.dispatchEvent(new CustomEvent('tagixo:save-error', {
                detail: { message: '{{ __("Builder bridge unavailable") }}' }
            }))
            return
        }

        await livue.call('saveGlobalVariables', [e.detail.variables]);
    });

    window.addEventListener('tagixo:get-component-defaults', async (e) => {
        const livue = resolveBuilderLivueBridge()
        const defaults = livue?.call
            ? await livue.call('getComponentDefaults', [e.detail.type])
            : []
        window.dispatchEvent(new CustomEvent('tagixo:component-defaults', {
            detail: { type: e.detail.type, defaults: defaults }
        }));
    });

    window.addEventListener('tagixo:structure-changed', async (e) => {
        refreshBuilderStyles(e.detail.structure);
    });

    refreshBuilderStyles();

    const livue = resolveBuilderLivueBridge()

    if (livue?.on) {
        livue.on('tagixo:saved', () => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { type: 'success', message: '{{ __("Saved successfully") }}' }
            }));
        });

        livue.on('tagixo:save-error', (data) => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { type: 'error', message: data.message || '{{ __("Error while saving") }}' }
            }));
        });
    }

    document.addEventListener('livue:navigated', () => {
        refreshBuilderStyles()
        if (typeof window.initVisualBuilder === 'function') {
            window.initVisualBuilder();
        }
    });
</script>
@endscript
