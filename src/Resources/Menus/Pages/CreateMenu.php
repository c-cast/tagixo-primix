<?php

namespace Ccast\TagixoPrimix\Resources\Menus\Pages;

use Ccast\Tagixo\Models\Menu;
use Ccast\TagixoPrimix\Resources\Menus\Concerns\PersistsMenuItems;
use Ccast\TagixoPrimix\Resources\MenuResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Primix\Notifications\Notification;
use Primix\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    use PersistsMenuItems;

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

        $this->persistMenuItems($menu, is_array($items) ? $items : []);

        Notification::make()
            ->title(__('primix::panel.notifications.created'))
            ->success()
            ->send();

        $this->redirect(
            $this->getRedirectUrl($menu),
            navigate: true
        );
    }
}
