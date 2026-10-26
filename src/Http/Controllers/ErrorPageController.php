<?php

namespace Tagixo\Primix\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Tagixo\PageBuilder\Facades\PageBuilder;
use Tagixo\PageBuilder\Support\ErrorPages;
use Tagixo\Primix\Pages\ThemeBuilder;
use Tagixo\Primix\Resources\PageResource;

/**
 * Opens the error page of an HTTP status. The page is created here if it is
 * missing — the same lazy step as the body of a model template — and then the
 * browser goes to its builder, which opens it in the `error` context.
 */
class ErrorPageController extends Controller
{
    public function __invoke(int $status): RedirectResponse
    {
        abort_unless(ErrorPages::isSupported($status), 404);

        $type = PageResource::builderType();
        $existing = ErrorPages::find($status);

        if ($existing !== null) {
            $type->authorize('update', $existing);
        } else {
            $type->authorize('create', $type->modelClass());
        }

        $page = PageBuilder::ensureErrorPage($status);

        return redirect()->to(
            $page !== null ? PageResource::builderUrl($page) : ThemeBuilder::getUrl(),
        );
    }
}
