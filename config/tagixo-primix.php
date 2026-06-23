<?php

use Ccast\TagixoPrimix\Resources\Forms\FormResource;
use Ccast\TagixoPrimix\Resources\GlobalBlocks\GlobalBlockResource;
use Ccast\TagixoPrimix\Resources\LayoutResource;
use Ccast\TagixoPrimix\Resources\Mails\MailResource;
use Ccast\TagixoPrimix\Resources\MediaResource;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;
use Ccast\TagixoPrimix\Resources\Popups\PopupResource;
use Ccast\TagixoPrimix\Resources\Sliders\SliderResource;

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Resources (Builders)
    |--------------------------------------------------------------------------
    |
    | Each entry below registers one Tagixo builder as a Primix admin resource
    | (its own navigation item + list/create/edit screens).
    |
    | To HIDE a builder from the admin panel, simply COMMENT OUT its line. The
    | underlying feature keeps working everywhere else (rendering, the visual
    | builder, the database) — you only remove its dedicated admin section.
    |
    | The order of this array is the order resources are registered.
    |
    */
    'resources' => [

        PageResource::class,
        LayoutResource::class,
        MenuResource::class,
        FormResource::class,
        SliderResource::class,
        PopupResource::class,

        /*
        | Global Blocks
        |
        | Reusable blocks you save once (save-to-library) and reference across
        | many pages. Two ways to manage them:
        |
        |   - Keep this line ENABLED to also get a dedicated admin resource for
        |     editing global blocks outside of any page.
        |   - COMMENT IT OUT to manage global blocks ONLY from inside the
        |     builder. References keep hydrating live either way — you just lose
        |     the standalone admin section.
        */
        GlobalBlockResource::class,

        /*
        | Optional resources — disabled by default.
        |
        | Uncomment to enable, or use the equivalent plugin methods:
        |   ->withMediaGallery()   ->withMailTemplates()   ->withPdfTemplates()
        |
        | Enabling here and via the method is safe (registered only once).
        */
        // MediaResource::class,
        // MailResource::class,
        // PdfResource::class,

    ],

];
