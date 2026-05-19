<?php

namespace Ccast\TagixoPrimix\Resources\Pages\Pages;

use Ccast\Tagixo\Enums\PageStatus;
use Ccast\Tagixo\Models\Page as PageModel;
use Ccast\TagixoPrimix\Resources\Pages\PageResource;
use Illuminate\Validation\Rule;
use Primix\Actions\Action;
use Primix\Notifications\Notification;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static ?string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openVisualBuilder')
                ->label(__('Visual Builder'))
                ->icon('heroicon-o-paint-brush')
                ->color('primary')
                ->url(fn () => PageResource::getUrl('build', ['record' => $this->record])),

            Action::make('publish')
                ->label(__('Publish'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->publish())
                ->visible(fn () => $this->record->status !== PageStatus::Published),

            Action::make('unpublish')
                ->label(__('Unpublish'))
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->action(fn () => $this->record->unpublish())
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
            Rule::unique((new PageModel())->getTable(), 'slug')->ignore($this->record),
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
