<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\Tagixo\Services\MenuItemsTreePersister;
use Ccast\Tagixo\Support\MenuTreeStructure;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\ManagesMenuTree;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Illuminate\Validation\Rule;
use Primix\Notifications\Notification;
use Primix\Resources\Actions\DeleteAction;
use Primix\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    use ManagesMenuTree;

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
        $tree = app(MenuItemsTreePersister::class)->toTree($record);
        $tree = $this->restorePageIds($tree);
        $data['items'] = MenuTreeStructure::treeToFlat($tree);

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

        $tree = MenuTreeStructure::flatToTree(is_array($items) ? $items : []);
        $tree = $this->foldPageIds($tree);
        app(MenuItemsTreePersister::class)->persist($this->record, $tree);

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

    /**
     * Fold target_page_id into target_value before handing the tree to the core persister.
     */
    private function foldPageIds(array $items): array
    {
        return array_map(function (array $item) {
            if (($item['target_type'] ?? null) === 'page' && ! empty($item['target_page_id'])) {
                $item['target_value'] = $item['target_page_id'];
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->foldPageIds($item['children']);
            }
            return $item;
        }, $items);
    }

    /**
     * Restore target_page_id from target_value for the Primix page picker.
     */
    private function restorePageIds(array $items): array
    {
        return array_map(function (array $item) {
            if (($item['target_type'] ?? null) === 'page' && ! empty($item['target_value'])) {
                $item['target_page_id'] = is_numeric($item['target_value'])
                    ? (int) $item['target_value']
                    : Page::where('slug', $item['target_value'])->value('id');
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->restorePageIds($item['children']);
            }
            return $item;
        }, $items);
    }
}
