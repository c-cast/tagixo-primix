{{--
    Templates of the site, zone by zone. A header and a footer belong to the
    template, so they open it; a body belongs to a page, and for a template
    scoped to a model that page is created on the way to the editor.
--}}
<x-primix::pages.page :page="$this">
    <div class="tgx-theme-templates">
        @forelse ($this->templates() as $template)
            <section class="tgx-theme-template">
                <header class="tgx-theme-template-head">
                    <div>
                        <h2 class="tgx-theme-template-name">
                            {{ $template->name }}
                            @if ($template->is_global)
                                <span class="tgx-theme-badge">{{ __('Global') }}</span>
                            @endif
                        </h2>
                        <p class="tgx-theme-template-conditions">{{ $this->conditionsSummary($template) }}</p>
                    </div>
                    <a class="tgx-theme-link" href="{{ \Tagixo\Primix\Resources\LayoutResource::getUrl('edit', ['record' => $template->getKey()]) }}">
                        {{ __('Settings') }}
                    </a>
                </header>

                <div class="tgx-theme-zones">
                    @foreach ($this->zones($template) as $zone)
                        <article class="tgx-theme-zone {{ $zone['configured'] ? 'is-configured' : '' }}">
                            <h3 class="tgx-theme-zone-label">{{ $zone['label'] }}</h3>
                            <p class="tgx-theme-zone-state">
                                {{ $zone['configured'] ? __('Built') : __('Empty') }}
                            </p>
                            <p class="tgx-theme-zone-description">{{ $zone['description'] }}</p>
                            @if ($zone['url'])
                                <a class="tgx-theme-zone-action" href="{{ $zone['url'] }}">
                                    {{ $zone['configured'] ? __('Edit') : __('Build') }}
                                </a>
                            @else
                                <span class="tgx-theme-zone-action is-disabled">{{ __('Not here') }}</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="tgx-theme-empty">{{ __('No template yet. A page without one wears nothing.') }}</p>
        @endforelse

        {{-- Error pages: one per HTTP status, built in a palette of static-safe
             modules. With static mode on each is a plain file, so it still answers
             when the database does not. --}}
        <section class="tgx-theme-template">
            <header class="tgx-theme-template-head">
                <div>
                    <h2 class="tgx-theme-template-name">{{ __('Error pages') }}</h2>
                    <p class="tgx-theme-template-conditions">
                        {{ $this->staticErrorPages()
                            ? __('Served from static files, without the database.')
                            : __('Rendered live. Turn on “Static error pages” in the site settings to serve them without the database.') }}
                    </p>
                </div>
            </header>

            <div class="tgx-theme-zones">
                @foreach ($this->errorPages() as $errorPage)
                    <article class="tgx-theme-zone {{ $errorPage['configured'] ? 'is-configured' : '' }}">
                        <h3 class="tgx-theme-zone-label">{{ $errorPage['label'] }}</h3>
                        <p class="tgx-theme-zone-state">
                            {{ $errorPage['configured'] ? __('Built') : __('Empty') }}
                        </p>
                        @if ($errorPage['exported'])
                            <p class="tgx-theme-zone-description">{{ __('Static file ready.') }}</p>
                        @endif
                        <a class="tgx-theme-zone-action" href="{{ $errorPage['url'] }}">
                            {{ $errorPage['configured'] ? __('Edit') : __('Build') }}
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-primix::pages.page>
