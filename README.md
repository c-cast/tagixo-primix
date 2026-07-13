# tagixo-primix

Primix SDK for the Tagixo Visual Builder.

This package wires Tagixo into the [Primix](https://github.com/livue-laravel/primix) admin panel: page, layout, menu and media resources, the media-gallery LiveWire component, and the supporting view layouts.

## Installation

```bash
composer require ccast/tagixo-primix
```

Requires:

- PHP ^8.2
- `ccast/tagixo`
- `primix/primix`
- `symfony/expression-language`

The package auto-registers `Ccast\TagixoPrimix\TagixoPrimixServiceProvider`.

## Usage

Enable the plugin in your `AdminPanelProvider`:

```php
use Ccast\TagixoPrimix\TagixoPrimixPlugin;

$panel->plugin(
    TagixoPrimixPlugin::make()->withMediaGallery(),
);
```

## Pages resource

The Pages resource lists both user-managed pages and the source-synced model template pages (no global `userManaged()` scope). A **Type** filter (`Pages` / `Model templates`) in the pages table separates the two kinds.

## Theme Builder

Behaviour is at parity with the Filament SDK (`getBuildUrl` / `isBodyConfigured` / `resolveModelPageTarget`): opening the Body of a model-scoped template lazily creates the underlying special page (`model_archive` → archive; `model_all` / `taxonomy` / `record` → single, via `Tagixo::ensureRoutePagesForModel()`) and opens its builder directly; the "configured" state reflects the actual page content.

## License

MIT
