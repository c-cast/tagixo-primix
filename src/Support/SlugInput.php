<?php

namespace Ccast\TagixoPrimix\Support;

use Closure;
use Illuminate\Support\Str;

class SlugInput
{
    /**
     * Watch callback for a title/name field that auto-fills a sibling slug
     * field from the typed value — but only while the slug is still empty.
     *
     * Fires on blur (not per keystroke) so the full value is slugified once,
     * and never clobbers a slug the user already set or one loaded on an
     * existing record (which would break published URLs). To regenerate, the
     * user can simply clear the slug field.
     *
     * The `$set`/`$get` arguments are intentionally untyped: Primix injects
     * `set` by name as an anonymous invokable setter (not a Closure/Set), and
     * resolves `get` to the Get utility — type-hinting either one throws.
     *
     * Usage:
     *   TextInput::make('title')->watchBlur(SlugInput::from());
     *   TextInput::make('slug');
     */
    public static function from(string $target = 'slug'): Closure
    {
        return function (mixed $state, $set, $get) use ($target): void {
            if (filled($get($target))) {
                return;
            }

            $set($target, Str::slug((string) $state));
        };
    }
}
