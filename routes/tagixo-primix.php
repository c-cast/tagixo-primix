<?php

use Illuminate\Support\Facades\Route;
use Tagixo\Primix\Http\Controllers\ErrorPageController;
use Tagixo\Primix\Http\Controllers\TemplateBodyController;

/*
|--------------------------------------------------------------------------
| Routes of the SDK
|--------------------------------------------------------------------------
|
| Only where a redirect has to happen server-side: the body of a model template
| and an error page are pages that may not exist yet, so they are created on the
| way to the editor.
|
*/

Route::middleware(
    config('tagixo.route_middleware', ['web', 'auth'])
)->group(function () {
    Route::get('/tagixo/primix/templates/{layout}/body', TemplateBodyController::class)
        ->name('tagixo-primix.templates.body');

    Route::get('/tagixo/primix/error-pages/{status}', ErrorPageController::class)
        ->whereNumber('status')
        ->name('tagixo-primix.error-pages.edit');
});
