<?php

namespace Ccast\TagixoPrimix\Resources\Pdfs\Pages;

use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;
use Primix\Resources\Actions\CreateAction;
use Primix\Resources\Pages\ListRecords;

class ListPdfs extends ListRecords
{
    protected static ?string $resource = PdfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
