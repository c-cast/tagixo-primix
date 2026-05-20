<?php

namespace Ccast\TagixoPrimix\Resources\Pdfs\Pages;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Models\PdfTemplate;
use Ccast\TagixoPrimix\Resources\Pdfs\PdfResource;
use Illuminate\Validation\Rule;
use Primix\Actions\Action;
use Primix\Notifications\Notification;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditPdf extends EditRecord
{
    protected static ?string $resource = PdfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openVisualBuilder')
                ->label(__('Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => PdfResource::getUrl('build', ['record' => $this->record])),

            Action::make('publish')
                ->label(__('Publish'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'status' => PageStatus::Published,
                        'published_at' => $this->record->published_at ?? now(),
                    ]);
                })
                ->visible(fn () => $this->record->status !== PageStatus::Published),

            Action::make('unpublish')
                ->label(__('Unpublish'))
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => PageStatus::Draft]))
                ->visible(fn () => $this->record->status === PageStatus::Published),

            DeleteAction::make(),
        ];
    }

    public function save(): void
    {
        $rules = $this->getFormValidationRules('form');
        $slugRules = $rules['data.slug'] ?? [];
        $slugRules = is_string($slugRules) ? explode('|', $slugRules) : $slugRules;

        $rules['data.slug'] = array_filter([
            ...$slugRules,
            Rule::unique((new PdfTemplate())->getTable(), 'slug')->ignore($this->record),
        ]);

        $this->validate($rules);

        $form = $this->getForm('form');

        $relationshipKeys = $form->getRelationshipKeys();
        $fileUploadKeys = $form->getFileUploadKeys();

        $data = $this->data;
        $form->dehydrateState($data);

        $attributeData = collect($data)
            ->except(array_merge($relationshipKeys, $fileUploadKeys))
            ->toArray();

        foreach ($fileUploadKeys as $key) {
            $value = data_get($data, $key);

            if ($value !== null) {
                data_set($attributeData, $key, $value);
            }
        }

        $this->record->update($attributeData);

        $form->saveRelationships($this->record, $data);

        $this->record->refresh();
        $this->data = $form->fillWithRelationships(
            $this->record->toArray(),
            $this->record,
        );

        Notification::make()
            ->title(__('primix::panel.notifications.saved'))
            ->success()
            ->send();
    }
}
