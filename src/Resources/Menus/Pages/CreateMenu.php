<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\Tagixo\Models\Page;
use Ccast\Tagixo\Services\MenuItemsTreePersister;
use Ccast\Tagixo\Support\MenuTreeStructure;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\ManagesMenuTree;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Primix\Notifications\Notification;
use Primix\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    use ManagesMenuTree;

    protected static ?string $resource = MenuResource::class;

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
            Rule::unique((new Menu())->getTable(), 'slug'),
        ]);

        $this->validate($rules);

        $form = $this->getForm();
        $data = $this->data;
        $form->dehydrateState($data);

        $items = $data['items'] ?? [];

        $attributeData = collect($data)
            ->except(['items'])
            ->toArray();

        /** @var Menu $menu */
        $menu = Menu::create($attributeData);

        $tree = MenuTreeStructure::flatToTree(is_array($items) ? $items : []);
        $tree = $this->foldPageIds($tree);
        app(MenuItemsTreePersister::class)->persist($menu, $tree);

        Notification::make()
            ->title(__('primix::panel.notifications.created'))
            ->success()
            ->send();

        $this->redirect(
            $this->getRedirectUrl($menu),
            navigate: true
        );
    }

    /**
     * Fold target_page_id into target_value before handing the tree to the core persister.
     * The core persister only knows target_value; target_page_id is a Primix picker field.
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
}
