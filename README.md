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

## License

MIT
