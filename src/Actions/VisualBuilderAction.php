<?php

namespace Ccast\TagixoPrimix\Actions;

use Primix\Actions\Action;

class VisualBuilderAction
{
    public static function make(\Closure|string $url): Action
    {
        return Action::make('visualBuilder')
            ->label(__('Open Visual Builder'))
            ->icon('heroicon-o-paint-brush')
            ->color('primary')
            ->url($url);
    }
}
