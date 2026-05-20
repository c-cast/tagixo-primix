<?php

namespace Ccast\TagixoPrimix\Resources\Mails\Pages;

use Ccast\TagixoPrimix\Resources\Mails\MailResource;
use Primix\Resources\Actions\CreateAction;
use Primix\Resources\Pages\ListRecords;

class ListMails extends ListRecords
{
    protected static ?string $resource = MailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
