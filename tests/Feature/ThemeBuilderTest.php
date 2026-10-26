<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Factories\UserFactory;
use Primix\Forms\Form;
use Primix\Tables\Table;
use Tagixo\Core\Facades\Tagixo;
use Tagixo\PageBuilder\Facades\PageBuilder;
use Tagixo\PageBuilder\Models\Layout;
use Tagixo\PageBuilder\Models\Page;
use Tagixo\PageBuilder\Services\ErrorPageSnapshot;
use Tagixo\PageBuilder\Support\ErrorPages;
use Tagixo\Primix\Pages\ThemeBuilder;
use Tagixo\Primix\Resources\LayoutResource;
use Tagixo\Primix\Resources\PageResource;

/*
 * The Theme Builder: every template with its three zones. A header and a footer
 * belong to the template and open it; a body belongs to a page, and for a
 * template scoped to a model that page is created on the way to the editor.
 */

uses(RefreshDatabase::class);

class ThemeArticle extends Model
{
    protected $table = 'tgx_test_theme_articles';

    protected $fillable = ['title'];
}

beforeEach(function () {
    Schema::create('tgx_test_theme_articles', function (Blueprint $table): void {
        $table->id();
        $table->string('title');
        $table->timestamps();
    });

    Tagixo::registerModels(['articles' => ['class' => ThemeArticle::class, 'label' => 'Articles']]);
    PageBuilder::setRouteFor(ThemeArticle::class, 'articles');

    $this->actingAs(UserFactory::new()->create());
});

it('carries the Theme Builder while the page builder is installed', function () {
    expect(Route::has('primix.admin.theme-builder'))->toBeTrue()
        ->and(Route::has('tagixo-primix.templates.body'))->toBeTrue();
});

it('shows the three zones of a template, and where each one is edited', function () {
    $layout = Layout::create(['name' => 'Shop', 'conditions' => [['type' => 'all_pages']]]);

    $zones = collect(app(ThemeBuilder::class)->zones($layout))->keyBy('scope');

    expect($zones->keys()->all())->toBe(['header', 'body', 'footer'])
        // A header is the template's own document.
        ->and($zones['header']['url'])->toContain('type=layouts')
        ->and($zones['header']['url'])->toContain('scope=header')
        ->and($zones['header']['configured'])->toBeFalse()
        ->and($zones['footer']['url'])->toContain('scope=footer')
        // The body of an ordinary template belongs to each page.
        ->and($zones['body']['editable'])->toBeFalse()
        ->and($zones['body']['url'])->toBeNull()
        ->and($zones['body']['description'])->toContain('own body');
});

it('says a zone is built once it holds something', function () {
    $layout = Layout::create([
        'name' => 'Shop',
        'header_content' => ['components' => [['id' => 'h']], 'body' => []],
        'header_rendered_html' => '<header>Shop</header>',
    ]);

    $zones = collect(app(ThemeBuilder::class)->zones($layout))->keyBy('scope');

    expect($zones['header']['configured'])->toBeTrue()
        ->and($zones['footer']['configured'])->toBeFalse();
});

it('sends the body of a model template through the route that creates its page', function () {
    $layout = Layout::create([
        'name' => 'Articles',
        'conditions' => [['type' => 'model_archive', 'model' => 'articles']],
    ]);

    $body = collect(app(ThemeBuilder::class)->zones($layout))->keyBy('scope')['body'];

    expect($body['editable'])->toBeTrue()
        ->and($body['url'])->toBe(route('tagixo-primix.templates.body', ['layout' => $layout->getKey()]))
        ->and($body['configured'])->toBeFalse();

    // Following it creates the archive page of that model and opens its builder.
    $page = PageBuilder::findRoutePagesForModel('articles')['archive'];
    expect($page)->toBeNull();

    $this->get($body['url'])->assertRedirect();

    $page = PageBuilder::findRoutePagesForModel('articles')['archive'];

    expect($page)->not->toBeNull()
        ->and($this->get($body['url'])->headers->get('location'))
        ->toContain('type=pages')
        ->toContain('id='.$page->getKey());
});

it('sends an ordinary template back to the Theme Builder instead of inventing a page', function () {
    $layout = Layout::create(['name' => 'Everything', 'conditions' => [['type' => 'all_pages']]]);

    $this->get(route('tagixo-primix.templates.body', ['layout' => $layout->getKey()]))
        ->assertRedirect(ThemeBuilder::getUrl());
});

it('reads the templates global first, and says what each claims', function () {
    Layout::create(['name' => 'Zebra', 'conditions' => [['type' => 'homepage']]]);
    Layout::create(['name' => 'Global', 'is_global' => true]);

    $page = app(ThemeBuilder::class);
    $templates = $page->templates();

    expect($templates->pluck('name')->all())->toBe(['Global', 'Zebra'])
        ->and($page->conditionsSummary($templates[0]))->toBe('Everything else')
        ->and($page->conditionsSummary($templates[1]))->toBe('Homepage');
});

it('renders the screen with the templates and their zones', function () {
    Layout::create(['name' => 'Shop', 'conditions' => [['type' => 'homepage']]]);

    $this->get(ThemeBuilder::getUrl())
        ->assertOk()
        ->assertSee('Theme Builder')
        ->assertSee('Shop')
        ->assertSee('Homepage')
        ->assertSee('Header')
        ->assertSee('Body')
        ->assertSee('Footer');
});

describe('error pages', function () {
    beforeEach(function () {
        $this->snapshotDir = sys_get_temp_dir().'/tgx-theme-errors-'.uniqid();
        config()->set('tagixo-page-builder.error_pages.snapshot_path', $this->snapshotDir);
    });

    afterEach(function () {
        File::deleteDirectory($this->snapshotDir);
    });

    it('lists one error page per HTTP status, none built yet', function () {
        $pages = collect(app(ThemeBuilder::class)->errorPages())->keyBy('status');

        expect($pages->keys()->all())->toBe(ErrorPages::statuses())
            ->and($pages[404]['label'])->toBe('Page not found (404)')
            ->and($pages[404]['configured'])->toBeFalse()
            ->and($pages[404]['exported'])->toBeFalse()
            ->and($pages[404]['url'])->toBe(route('tagixo-primix.error-pages.edit', ['status' => 404]));

        // Listing never creates anything.
        expect(Page::where('template_type', 'error')->count())->toBe(0);
    });

    it('creates the page on the way to the editor, which opens it', function () {
        $url = route('tagixo-primix.error-pages.edit', ['status' => 503]);

        $this->get($url)->assertRedirect();

        $page = ErrorPages::find(503);

        expect($page)->not->toBeNull()
            ->and($this->get($url)->headers->get('location'))
            ->toContain('type=pages')
            ->toContain('id='.$page->getKey())
            // Following it twice is the same page.
            ->and(Page::where('template_type', 'error')->count())->toBe(1);
    });

    it('opens an error page in the error context', function () {
        $page = PageBuilder::ensureErrorPage(404);
        $mount = PageResource::builderType()->editor($page);

        expect($mount->context)->toBe('error');
    });

    it('does not invent a page for a status it does not answer', function () {
        $this->get('/tagixo/primix/error-pages/418')->assertNotFound();

        expect(Page::where('template_type', 'error')->count())->toBe(0);
    });

    it('says a page is built once it holds something, and that its file is ready', function () {
        app(ErrorPageSnapshot::class)->setEnabled(true);
        $page = ErrorPages::ensure(404);
        $page->update(['rendered_html' => '<section>Nothing here</section>']);

        $row = collect(app(ThemeBuilder::class)->errorPages())->keyBy('status')[404];

        expect($row['configured'])->toBeTrue()
            ->and($row['exported'])->toBeTrue()
            ->and(app(ThemeBuilder::class)->staticErrorPages())->toBeTrue();
    });

    it('shows them on the screen, and what the mode is', function () {
        $this->get(ThemeBuilder::getUrl())
            ->assertOk()
            ->assertSee('Error pages')
            ->assertSee('Page not found (404)')
            ->assertSee('Maintenance (503)')
            ->assertSee('Rendered live');
    });

    it('keeps them out of the pages listing', function () {
        ErrorPages::ensure(404);
        $home = PageResource::createRecord(['title' => 'Home']);

        $ids = PageResource::builderType()->indexQuery(request())->pluck('id')->all();

        expect($ids)->toBe([$home->getKey()]);
    });
});

it('opens one editor per document of a layout, and keeps the rest of the actions', function () {
    $actions = array_map(
        static fn ($action): string => $action->getName(),
        LayoutResource::table(new Table)->getActions(),
    );

    expect($actions)->toBe(['header', 'footer', 'edit', 'delete'])
        ->and(LayoutResource::builderUrl(Layout::create(['name' => 'Shop']), 'footer'))
        ->toContain('scope=footer');
});

it('administers a layout with its conditions on top of the declared fields', function () {
    $fields = array_map(
        static fn ($component): string => $component->getName(),
        LayoutResource::form(new Form)->getComponents(),
    );

    // name and is_global come from LayoutType::fields(); what the template
    // claims is the resource's own, because only a layout claims anything.
    expect($fields)->toBe([
        'name', 'is_global',
        'condition_all_pages', 'condition_homepage', 'condition_pages', 'condition_models', 'condition_model_archives',
    ]);
});
