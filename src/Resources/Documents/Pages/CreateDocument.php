<?php

namespace Ccast\TagixoPrimix\Resources\Documents\Pages;

use Ccast\Tagixo\Models\DocumentTemplate;
use Ccast\TagixoPrimix\Resources\Documents\DocumentResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Primix\Notifications\Notification;
use Primix\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static ?string $resource = DocumentResource::class;

    protected function getRedirectUrl(Model $record): string
    {
        return $this->resolveResource()::getUrl('edit', ['record' => $record->getKey()]);
    }

    public function create(): void
    {
        $rules = $this->getFormValidationRules('form');
        $slugRules = $rules['data.slug'] ?? [];
        $slugRules = is_string($slugRules) ? explode('|', $slugRules) : $slugRules;

        $rules['data.slug'] = array_filter([
            ...$slugRules,
            Rule::unique((new DocumentTemplate())->getTable(), 'slug'),
        ]);

        $this->validate($rules);

        $resource = $this->resolveResource();
        $model = $resource::getModel();
        $form = $this->getForm();

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

        $attributeData['content'] = $attributeData['content'] ?? $this->getDefaultStructure();

        $record = $model::create($attributeData);

        $this->afterCreate($record);

        $form->saveRelationships($record, $data);

        Notification::make()
            ->title(__('primix::panel.notifications.created'))
            ->success()
            ->send();

        $this->redirect(
            $this->getRedirectUrl($record),
            navigate: true
        );
    }

    protected function afterCreate(Model $record): void
    {
        if (empty($record->content)) {
            $record->update(['content' => $this->getDefaultStructure()]);
        }
    }

    protected function getDefaultStructure(): array
    {
        return [
            'components' => [],
            'body' => [],
        ];
    }
}
