<x-primix::pages.page :page="$this">
    <div class="space-y-6">

        {{-- Header bar --}}
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Manage your site templates and assign them to page types.') }}
            </p>
            <button
                @click="handleOpenCreate()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                {{ __('New Template') }}
            </button>
        </div>

        {{-- Template grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;">

            @foreach ($this->getLayouts() as $layout)
                <div class="flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                    {{-- Card header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <span class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $layout->name }}</span>
                        @if ($layout->is_global)
                            <span class="ml-2 shrink-0 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 px-2 py-0.5 rounded-full">
                                {{ __('Global') }}
                            </span>
                        @endif
                    </div>

                    {{-- Sections --}}
                    <div class="flex flex-col gap-2 px-4 py-3">

                        {{-- Header --}}
                        @if ($layout->header_rendered_html)
                            <a href="{{ $this->getBuildUrl($layout->id, 'header') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                style="background:#059669;color:#fff;">
                                <x-heroicon-o-paint-brush class="w-4 h-4 shrink-0"/>{{ __('Header') }}
                            </a>
                        @else
                            <a href="{{ $this->getBuildUrl($layout->id, 'header') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed transition-colors"
                                style="border-color:#d1d5db;color:#6b7280;">
                                <x-heroicon-o-plus class="w-4 h-4 shrink-0"/>{{ __('Header') }}
                            </a>
                        @endif

                        {{-- Body --}}
                        @if ($layout->is_global)
                            <span class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed cursor-not-allowed"
                                style="border-color:#e5e7eb;color:#d1d5db;">
                                <x-heroicon-o-minus class="w-4 h-4 shrink-0"/>{{ __('Body') }}
                            </span>
                        @elseif ($this->isBodyConfigured($layout))
                            <a href="{{ $this->getBuildUrl($layout->id, 'body') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                style="background:#059669;color:#fff;">
                                <x-heroicon-o-paint-brush class="w-4 h-4 shrink-0"/>{{ __('Body') }}
                            </a>
                        @else
                            <a href="{{ $this->getBuildUrl($layout->id, 'body') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed transition-colors"
                                style="border-color:#d1d5db;color:#6b7280;">
                                <x-heroicon-o-plus class="w-4 h-4 shrink-0"/>{{ __('Body') }}
                            </a>
                        @endif

                        {{-- Footer --}}
                        @if ($layout->footer_rendered_html)
                            <a href="{{ $this->getBuildUrl($layout->id, 'footer') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                style="background:#059669;color:#fff;">
                                <x-heroicon-o-paint-brush class="w-4 h-4 shrink-0"/>{{ __('Footer') }}
                            </a>
                        @else
                            <a href="{{ $this->getBuildUrl($layout->id, 'footer') }}"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium rounded-lg border-2 border-dashed transition-colors"
                                style="border-color:#d1d5db;color:#6b7280;">
                                <x-heroicon-o-plus class="w-4 h-4 shrink-0"/>{{ __('Footer') }}
                            </a>
                        @endif

                    </div>

                    {{-- Conditions badges --}}
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex-1">
                        @if ($layout->is_global)
                            <span class="inline-flex text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                {{ __('Default fallback') }}
                            </span>
                        @elseif ($layout->conditions && count($layout->conditions) > 0)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($layout->conditions as $condition)
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">
                                        {{ $this->getConditionLabel($condition) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs italic text-gray-400 dark:text-gray-500">{{ __('No conditions set') }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    @if (! $layout->is_global)
                        <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                            <button @click="handleOpenEdit({{ $layout->id }})"
                                class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <x-heroicon-o-cog-6-tooth class="w-4 h-4"/>
                                {{ __('Settings') }}
                            </button>
                            <button v-click.confirm="['deleteLayout', '{{ $layout->id }}']"
                                class="inline-flex items-center gap-1.5 text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 px-2 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors ml-auto">
                                <x-heroicon-o-trash class="w-4 h-4"/>
                                {{ __('Delete') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Add new template card --}}
            <button @click="handleOpenCreate()"
                class="flex flex-col items-center justify-center gap-3 p-8 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 text-gray-400 dark:text-gray-500 hover:border-blue-400 hover:text-blue-500 dark:hover:border-blue-500 dark:hover:text-blue-400 transition-colors cursor-pointer"
                style="min-height:200px;">
                <x-heroicon-o-plus-circle class="w-10 h-10"/>
                <span class="text-sm font-medium">{{ __('New Template') }}</span>
            </button>
        </div>

        {{-- Modal --}}
        <div v-if="showModal" class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4" @click.self="handleClose()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[90vh]">

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span v-if="editingLayoutId">{{ __('Template Settings') }}</span>
                        <span v-else>{{ __('New Template') }}</span>
                    </h3>
                    <button @click="handleClose()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5"/>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="px-6 py-5 space-y-5 overflow-y-auto flex-1">

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Template Name') }}</label>
                        <input type="text" v-model="localName"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            placeholder="{{ __('e.g. Blog Template') }}">
                    </div>

                    <hr class="border-gray-100 dark:border-gray-700">

                    {{-- Conditions --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Use On') }}</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">{{ __('This template applies when any of the following conditions match.') }}</p>

                        <div class="space-y-3">

                            {{-- ── PAGES section ── --}}
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl">
                                <button type="button" @click="toggleGroup('pages')"
                                    class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-700/60 rounded-t-xl text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/80 transition-colors">
                                    <span>{{ __('Pages') }}</span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="isGroupOpen('pages') ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>

                                <div v-show="isGroupOpen('pages')">
                                {{-- Homepage --}}
                                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                    <input type="checkbox" :checked="hasHomepage" @change="toggleHomepage()"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Homepage') }}</span>
                                </label>

                                {{-- Specific Pages checkbox --}}
                                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                    <input type="checkbox" :checked="specificPagesOpen" @change="toggleSpecificPages()"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Specific Pages') }}</span>
                                </label>

                                {{-- Specific Pages search (shown when checkbox is on) --}}
                                <div v-if="specificPagesOpen" class="px-4 py-3 space-y-2 border-t border-gray-100 dark:border-gray-700/50">
                                    <div class="flex flex-wrap gap-1.5 mt-2" v-if="pageConditions.length > 0">
                                        <span v-for="c in pageConditions" :key="c.value"
                                            class="inline-flex items-center gap-1 text-xs bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 pl-2 pr-1 py-0.5 rounded-full">
                                            @{{ c.label || ('#' + c.value) }}
                                            <button type="button" @click="removePageCondition(c.value)" class="ml-0.5 hover:text-blue-900 dark:hover:text-blue-100 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="relative">
                                        <input type="text" v-model="pageSearch" @input="doSearchPages()"
                                            placeholder="{{ __('Search pages…') }}"
                                            class="w-full text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <div v-if="pageResults.length > 0"
                                            class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                                            <button type="button" v-for="p in pageResults" :key="p.id" @click="addPageCondition(p)"
                                                class="w-full text-left text-sm px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors">
                                                @{{ p.title }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            {{-- ── MODEL / TAXONOMY sections ── --}}
                            @foreach ($this->getConditionTree() as $modelKey => $modelDef)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl">
                                <button type="button" @click="toggleGroup('{{ $modelKey }}')"
                                    class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-700/60 rounded-t-xl text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/80 transition-colors">
                                    <span>{{ $modelDef['label'] }}</span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="isGroupOpen('{{ $modelKey }}') ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>

                                <div v-show="isGroupOpen('{{ $modelKey }}')">
                                {{-- All [model] checkbox — mutually exclusive with the others --}}
                                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                    <input type="checkbox"
                                        :checked="hasModelAll('{{ $modelKey }}')"
                                        @change="toggleModelAll('{{ $modelKey }}')"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('All') }} {{ $modelDef['label'] }}</span>
                                </label>

                                {{-- Archive [model] checkbox — mutually exclusive with the others --}}
                                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                    <input type="checkbox"
                                        :checked="hasModelArchive('{{ $modelKey }}')"
                                        @change="toggleModelArchive('{{ $modelKey }}')"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Archive') }} {{ $modelDef['label'] }}</span>
                                </label>

                                {{-- Taxonomy sub-rows (only for non-taxonomy models) --}}
                                @if (! $modelDef['is_taxonomy'])
                                    @foreach ($modelDef['taxonomies'] as $taxKey => $tax)
                                    {{-- "By [Taxonomy]" checkbox --}}
                                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                        <input type="checkbox"
                                            :checked="isTaxOpen('{{ $modelKey }}', '{{ $taxKey }}')"
                                            @change="toggleTaxSection('{{ $modelKey }}', '{{ $taxKey }}')"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('By') }} {{ $tax['label'] }}</span>
                                    </label>

                                    {{-- Taxonomy search (shown when checkbox is on) --}}
                                    <div v-if="isTaxOpen('{{ $modelKey }}', '{{ $taxKey }}')"
                                        class="px-4 py-3 space-y-2 border-t border-gray-100 dark:border-gray-700/50">
                                        <div class="flex flex-wrap gap-1.5 mt-2" v-if="getTaxTerms('{{ $modelKey }}', '{{ $taxKey }}').length > 0">
                                            <span v-for="c in getTaxTerms('{{ $modelKey }}', '{{ $taxKey }}')" :key="c.term_id"
                                                class="inline-flex items-center gap-1 text-xs bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 pl-2 pr-1 py-0.5 rounded-full">
                                                @{{ c.term_label || ('#' + c.term_id) }}
                                                <button type="button" @click="removeTaxTerm('{{ $modelKey }}', '{{ $taxKey }}', c.term_id)" class="ml-0.5 hover:text-purple-900 dark:hover:text-purple-100 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                </button>
                                            </span>
                                        </div>
                                        <div class="relative">
                                            <input type="text" @input="doSearchTax('{{ $taxKey }}', $event.target.value)"
                                                placeholder="{{ __('Search') }} {{ $tax['label'] }}…"
                                                class="w-full text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <div v-if="taxResults['{{ $taxKey }}'] && taxResults['{{ $taxKey }}'].length > 0"
                                                class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                                                <button type="button" v-for="t in taxResults['{{ $taxKey }}']" :key="t.id" @click="addTaxTerm('{{ $modelKey }}', '{{ $taxKey }}', t)"
                                                    class="w-full text-left text-sm px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors">
                                                    @{{ t.label }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif

                                {{-- Specific [model] checkbox --}}
                                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer border-t border-gray-100 dark:border-gray-700/50">
                                    <input type="checkbox"
                                        :checked="isSpecificModelOpen('{{ $modelKey }}')"
                                        @change="toggleSpecificModel('{{ $modelKey }}')"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Specific') }} {{ $modelDef['label'] }}</span>
                                </label>

                                {{-- Specific record search (shown when checkbox is on) --}}
                                <div v-if="isSpecificModelOpen('{{ $modelKey }}')"
                                    class="px-4 py-3 space-y-2 border-t border-gray-100 dark:border-gray-700/50">
                                    <div class="flex flex-wrap gap-1.5 mt-2" v-if="getModelRecords('{{ $modelKey }}').length > 0">
                                        <span v-for="c in getModelRecords('{{ $modelKey }}')" :key="c.model_id"
                                            class="inline-flex items-center gap-1 text-xs bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 pl-2 pr-1 py-0.5 rounded-full">
                                            @{{ c.record_label || ('#' + c.model_id) }}
                                            <button type="button" @click="removeModelRecord('{{ $modelKey }}', c.model_id)" class="ml-0.5 hover:text-emerald-900 dark:hover:text-emerald-100 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="relative">
                                        <input type="text" @input="doSearchRecords('{{ $modelKey }}', $event.target.value)"
                                            placeholder="{{ __('Search') }} {{ $modelDef['label'] }}…"
                                            class="w-full text-sm px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <div v-if="recordResults['{{ $modelKey }}'] && recordResults['{{ $modelKey }}'].length > 0"
                                            class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
                                            <button type="button" v-for="r in recordResults['{{ $modelKey }}']" :key="r.id" @click="addModelRecord('{{ $modelKey }}', r)"
                                                class="w-full text-left text-sm px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors">
                                                @{{ r.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>

                {{-- Modal footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 shrink-0">
                    <button @click="handleClose()"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button @click="handleSave()"
                        class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-primix::pages.page>

@script
<script>
const localName = ref('');
const localConditions = ref([]);
const sectionOpen = ref({});
const groupOpen = ref({});
const pageSearch = ref('');
const pageResults = ref([]);
const taxResults = ref({});
const recordResults = ref({});

// ── Computed ──────────────────────────────────────────────────────────────────

const hasHomepage = computed(() => localConditions.value.some(c => c.type === 'homepage'));
const pageConditions = computed(() => localConditions.value.filter(c => c.type === 'page_id'));

// "Specific Pages" section is open if manually toggled on OR if there are already page_id conditions
const specificPagesOpen = computed(() =>
    sectionOpen.value['pages__specific'] !== undefined
        ? sectionOpen.value['pages__specific']
        : pageConditions.value.length > 0
);

// ── Section open helpers (reactive because they read sectionOpen + localConditions) ──

function isTaxOpen(modelKey, taxKey) {
    const key = modelKey + '__tax__' + taxKey;
    return sectionOpen.value[key] !== undefined
        ? sectionOpen.value[key]
        : localConditions.value.some(c => c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey);
}

function isSpecificModelOpen(modelKey) {
    const key = modelKey + '__specific';
    return sectionOpen.value[key] !== undefined
        ? sectionOpen.value[key]
        : localConditions.value.some(c => c.type === 'model_record' && c.model === modelKey);
}

// ── Condition data helpers ────────────────────────────────────────────────────

function hasModelAll(modelKey) {
    return localConditions.value.some(c => c.type === 'model_all' && c.model === modelKey);
}
function hasModelArchive(modelKey) {
    return localConditions.value.some(c => c.type === 'model_archive' && c.model === modelKey);
}
function getTaxTerms(modelKey, taxKey) {
    return localConditions.value.filter(c => c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey);
}
function getModelRecords(modelKey) {
    return localConditions.value.filter(c => c.type === 'model_record' && c.model === modelKey);
}

// ── Reset helpers ─────────────────────────────────────────────────────────────

function isGroupOpen(key) {
    if (groupOpen.value[key] !== undefined) return groupOpen.value[key];
    if (key === 'pages') return hasHomepage.value || pageConditions.value.length > 0;
    return localConditions.value.some(c => c.model === key);
}

function toggleGroup(key) {
    groupOpen.value = { ...groupOpen.value, [key]: !isGroupOpen(key) };
}

function resetModalState() {
    localName.value = '';
    localConditions.value = [];
    sectionOpen.value = {};
    groupOpen.value = {};
    pageSearch.value = '';
    pageResults.value = [];
    taxResults.value = {};
    recordResults.value = {};
}

// ── Server modal calls ────────────────────────────────────────────────────────

async function handleOpenCreate() {
    resetModalState();
    await livue.call('openCreateModal');
}

async function handleOpenEdit(id) {
    resetModalState();
    const data = await livue.call('openEditModal', [id]);
    localName.value = data.name;
    localConditions.value = JSON.parse(JSON.stringify(data.conditions || []));
    // sectionOpen stays {} so open/closed state is derived from existing conditions
}

async function handleSave() {
    await livue.call('saveModal', [localName.value, localConditions.value]);
}

async function handleClose() {
    resetModalState();
    await livue.call('closeModal');
}

// ── Toggle: Homepage ──────────────────────────────────────────────────────────

function toggleHomepage() {
    if (hasHomepage.value) {
        localConditions.value = localConditions.value.filter(c => c.type !== 'homepage');
    } else {
        localConditions.value.push({ type: 'homepage' });
    }
}

// ── Toggle: Specific Pages ────────────────────────────────────────────────────

function toggleSpecificPages() {
    if (specificPagesOpen.value) {
        localConditions.value = localConditions.value.filter(c => c.type !== 'page_id');
        sectionOpen.value = { ...sectionOpen.value, 'pages__specific': false };
    } else {
        sectionOpen.value = { ...sectionOpen.value, 'pages__specific': true };
    }
}

async function doSearchPages() {
    if (pageSearch.value.length < 2) { pageResults.value = []; return; }
    pageResults.value = await livue.call('searchPages', [pageSearch.value]);
}

function addPageCondition(page) {
    if (!pageConditions.value.some(c => String(c.value) === String(page.id))) {
        localConditions.value.push({ type: 'page_id', value: page.id, label: page.title });
    }
    pageSearch.value = '';
    pageResults.value = [];
}

function removePageCondition(pageId) {
    localConditions.value = localConditions.value.filter(c =>
        !(c.type === 'page_id' && String(c.value) === String(pageId))
    );
}

// ── Toggle: All [Model]  (mutually exclusive) ─────────────────────────────────

function toggleModelAll(modelKey) {
    if (hasModelAll(modelKey)) {
        localConditions.value = localConditions.value.filter(c =>
            !(c.type === 'model_all' && c.model === modelKey)
        );
    } else {
        // Remove all other conditions for this model (mutually exclusive)
        localConditions.value = localConditions.value.filter(c =>
            !((c.type === 'model_taxonomy' || c.type === 'model_record' || c.type === 'model_archive') && c.model === modelKey)
        );
        // Close all sub-sections for this model
        const updated = { ...sectionOpen.value };
        Object.keys(updated).forEach(k => { if (k.startsWith(modelKey + '__')) delete updated[k]; });
        sectionOpen.value = updated;
        localConditions.value.push({ type: 'model_all', model: modelKey });
    }
}

function toggleModelArchive(modelKey) {
    if (hasModelArchive(modelKey)) {
        localConditions.value = localConditions.value.filter(c =>
            !(c.type === 'model_archive' && c.model === modelKey)
        );
    } else {
        // Remove all other conditions for this model (mutually exclusive)
        localConditions.value = localConditions.value.filter(c =>
            !((c.type === 'model_all' || c.type === 'model_taxonomy' || c.type === 'model_record') && c.model === modelKey)
        );
        const updated = { ...sectionOpen.value };
        Object.keys(updated).forEach(k => { if (k.startsWith(modelKey + '__')) delete updated[k]; });
        sectionOpen.value = updated;
        localConditions.value.push({ type: 'model_archive', model: modelKey });
    }
}

// ── Toggle: By [Taxonomy] section ─────────────────────────────────────────────

function toggleTaxSection(modelKey, taxKey) {
    const key = modelKey + '__tax__' + taxKey;
    if (isTaxOpen(modelKey, taxKey)) {
        localConditions.value = localConditions.value.filter(c =>
            !(c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey)
        );
        sectionOpen.value = { ...sectionOpen.value, [key]: false };
    } else {
        // If "All" or "Archive" was checked, uncheck it
        localConditions.value = localConditions.value.filter(c =>
            !((c.type === 'model_all' || c.type === 'model_archive') && c.model === modelKey)
        );
        sectionOpen.value = { ...sectionOpen.value, [key]: true };
    }
}

async function doSearchTax(taxKey, query) {
    if (query.length < 2) { taxResults.value = { ...taxResults.value, [taxKey]: [] }; return; }
    const res = await livue.call('searchTaxonomyTerms', [taxKey, query]);
    taxResults.value = { ...taxResults.value, [taxKey]: res };
}

function addTaxTerm(modelKey, taxKey, term) {
    if (!getTaxTerms(modelKey, taxKey).some(c => String(c.term_id) === String(term.id))) {
        localConditions.value.push({ type: 'model_taxonomy', model: modelKey, taxonomy: taxKey, term_id: term.id, term_label: term.label });
    }
    taxResults.value = { ...taxResults.value, [taxKey]: [] };
}

function removeTaxTerm(modelKey, taxKey, termId) {
    localConditions.value = localConditions.value.filter(c =>
        !(c.type === 'model_taxonomy' && c.model === modelKey && c.taxonomy === taxKey && String(c.term_id) === String(termId))
    );
}

// ── Toggle: Specific [Model] section ──────────────────────────────────────────

function toggleSpecificModel(modelKey) {
    const key = modelKey + '__specific';
    if (isSpecificModelOpen(modelKey)) {
        localConditions.value = localConditions.value.filter(c =>
            !(c.type === 'model_record' && c.model === modelKey)
        );
        sectionOpen.value = { ...sectionOpen.value, [key]: false };
    } else {
        // If "All" or "Archive" was checked, uncheck it
        localConditions.value = localConditions.value.filter(c =>
            !((c.type === 'model_all' || c.type === 'model_archive') && c.model === modelKey)
        );
        sectionOpen.value = { ...sectionOpen.value, [key]: true };
    }
}

async function doSearchRecords(modelKey, query) {
    if (query.length < 2) { recordResults.value = { ...recordResults.value, [modelKey]: [] }; return; }
    const res = await livue.call('searchRecords', [modelKey, query]);
    recordResults.value = { ...recordResults.value, [modelKey]: res };
}

function addModelRecord(modelKey, record) {
    if (!getModelRecords(modelKey).some(c => String(c.model_id) === String(record.id))) {
        localConditions.value.push({ type: 'model_record', model: modelKey, model_id: record.id, record_label: record.label });
    }
    recordResults.value = { ...recordResults.value, [modelKey]: [] };
}

function removeModelRecord(modelKey, recordId) {
    localConditions.value = localConditions.value.filter(c =>
        !(c.type === 'model_record' && c.model === modelKey && String(c.model_id) === String(recordId))
    );
}

return {
    localName, localConditions, sectionOpen, groupOpen, pageSearch, pageResults, taxResults, recordResults,
    hasHomepage, pageConditions, specificPagesOpen,
    isGroupOpen, toggleGroup,
    handleOpenCreate, handleOpenEdit, handleSave, handleClose,
    hasModelAll, hasModelArchive, isTaxOpen, isSpecificModelOpen, getTaxTerms, getModelRecords,
    toggleHomepage, toggleSpecificPages, toggleModelAll, toggleModelArchive, toggleTaxSection, toggleSpecificModel,
    doSearchPages, addPageCondition, removePageCondition,
    doSearchTax, addTaxTerm, removeTaxTerm,
    doSearchRecords, addModelRecord, removeModelRecord,
};
</script>
@endscript
