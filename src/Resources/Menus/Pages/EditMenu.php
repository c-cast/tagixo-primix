<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\ManagesMenuTree;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Ccast\TagixoPrimix\Support\MenuTreeStructure;
use Illuminate\Validation\Rule;
use Primix\Notifications\Notification;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    use ManagesMenuTree;
    use PersistsMenuItems;

    protected static ?string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Menu $record */
        $record = $this->record;
        // Persistence stores a nested tree; the tree field edits a flat list
        // with per-item depth. Flatten on the way in.
        $data['items'] = MenuTreeStructure::treeToFlat($this->menuItemsToTree($record));

        return $data;
    }

    public function save(): void
    {
        $rules = $this->getFormValidationRules('form');
        $slugRules = $rules['data.slug'] ?? [];
        $slugRules = is_string($slugRules) ? explode('|', $slugRules) : $slugRules;

        $rules['data.slug'] = array_filter([
            ...$slugRules,
            Rule::unique((new Menu())->getTable(), 'slug')->ignore($this->record),
        ]);

        $this->validate($rules);

        $form = $this->getForm('form');
        $data = $this->data;
        $form->dehydrateState($data);

        $items = $data['items'] ?? [];

        $attributeData = collect($data)
            ->except(['items'])
            ->toArray();

        $this->record->update($attributeData);

        // Rebuild the nested tree from the flat (depth-carrying) editor state
        // before handing it to the persistence layer.
        $tree = MenuTreeStructure::flatToTree(is_array($items) ? $items : []);
        $this->persistMenuItems($this->record, $tree);

        $this->record->refresh();
        $this->data = $form->fillWithRelationships(
            $this->mutateFormDataBeforeFill($this->record->toArray()),
            $this->record,
        );

        Notification::make()
            ->title(__('primix::panel.notifications.saved'))
            ->success()
            ->send();
    }
}
