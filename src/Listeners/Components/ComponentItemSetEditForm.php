<?php

namespace FastDog\Config\Listeners\Components;


use FastDog\Config\Events\Components\ComponentItemAdminPrepare as PublicModulesItemAdminPrepareEvent;
use FastDog\Core\Events\GetComponentType;
use FastDog\Core\Models\Components;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Models\FormFieldTypes;
use Illuminate\Http\Request;

class ComponentItemSetEditForm
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * ContentAdminPrepare constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param PublicModulesItemAdminPrepareEvent $event
     */
    public function handle(PublicModulesItemAdminPrepareEvent $event)
    {
        /**
         * @var $item Components
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }
        /**
         * Парамтеры извлекаемые из json объекта data
         */
        $extractParameters = $item->getExtractParameterNames();

        foreach ($extractParameters as $name) {
            $data[$name] = (isset($data['data']->{$name})) ? $data['data']->{$name} : '';
        }

        $components = Components::getInstallModules();
        if (isset($components['_events'])) {
            array_merge($result['_events'], $components['_events']);
            unset($components['_events']);
        }


        $componentsPrepare = []; //<-- Конвертируем массив типов для отображения в списке в optiongroup
        $componentTemplates = [];//<-- Шаблоны текущего типа, необходимо установить для первого отображения в форме

        foreach ($components as $_key => &$component) {

            foreach ($component['items'] as &$field) {
                $field['id'] = $component['id'] . '::' . $field['id'];
                $type = $data[Components::TYPE];
                if (isset($data[Components::TYPE]->id)) {
                    $type = $data[Components::TYPE]->id;
                }
                if ($field['id'] == $type) {
                    foreach ($field['templates'] as $key => $template) {
                        $componentTemplates[] = [
                            'id' => $key,
                            'name' => $key,
                            'label' => $key,
                            'items' => $template['templates'],
                        ];
                    }
                }
            }


            $componentsPrepare[] = [
                'id' => $component['name'],
                'name' => $component['name'],
                'label' => $component['name'],
                'items' => $component['items'],
            ];

        }


        $defaultType = [
            [
                'id' => Components::NAME,
                'type' => FormFieldTypes::TYPE_TEXT,
                'name' => Components::NAME,
                'label' => trans('config::forms.components.general.fields.name'),
                'css_class' => 'col-sm-12',
                'form_group' => false,
                'required' => true,
                'validate' => 'required|min:5',
                'readonly' => ($item->id != 0),
            ],
            [
                'id' => Components::TYPE,
                'type' => FormFieldTypes::TYPE_SELECT,
                'name' => Components::TYPE,
                'label' => trans('config::forms.components.general.fields.type'),
                'css_class' => 'col-sm-6 m-t-xs',
                'form_group' => false,
                'items' => $componentsPrepare,
                'option_group' => true,
                //'readonly' => ($item->id > 0),
            ],
            [
                'id' => 'template',
                'type' => FormFieldTypes::TYPE_SELECT,
                'name' => 'template',
                'label' => trans('config::forms.components.general.fields.template'),
                'css_class' => 'col-sm-6 m-t-xs',
                'form_group' => false,
                'items' => $componentTemplates,
                'option_group' => true,
            ],
        ];
        event(new GetComponentType($defaultType));

        $result['form'] = [
            'create_url' => 'config/component/add',
            'update_url' => 'config/component/save',
            'help' => 'component_item',
            'tabs' => (array)[
                (object)[
                    'id' => 'catalog-item-general-tab',
                    'name' => trans('config::forms.components.general.title'),
                    'active' => true,
                    'fields' => $defaultType,

                    //  (array)[
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SELECT,
//                            'name' => 'item_id',
//                            'form_group' => false,
//                            'label' => trans('app.Пункт меню навигации'),
//                            'items' => $this->getMenuTree(),
//                            'option_group' => false,
//                            'expression' => 'function(item){ return (item.type.id == "menu::item") }',
//                        ],
//                        [
//                            'id' => 'html',
//                            'type' => FormFieldTypes::TYPE_CODE_EDITOR,
//                            'name' => 'html',
//                            'css_class' => 'col-sm-12 m-t-xs',
//                            'label' => trans('app.Html содержимое'),
//                            'expression' => 'function(item){ return (item.type.id == "core::html") }',
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SELECT,
//                            'name' => 'item_id',
//                            'form_group' => false,
//                            'label' => trans('app.Справочник'),
//                            'items' => $this->getDataSourceItems(),
//                            'option_group' => false,
//                            'expression' => 'function(item){
//                            return (item.type.id == "data_source::items" || item.type.id == "data_source::pages")
//                            }',
//                        ],
//                        [
//                            'id' => 'data_source_item_id',
//                            'type' => FormFieldTypes::TYPE_SEARCH,
//                            'name' => 'data_source_item_id',
//                            'label' => trans('app.Элемент справочника'),
//                            'css_class' => 'col-sm-6',
//                            'data_url' => 'data-source/value-list',
//                            'form_group' => false,
//                            'title' => trans('app.Список элементов'),
//                            'expression' => 'function(item){ return (item.type.id == "data_source::pages") }',
//                            'filter' => [
//                                'id' => (isset($data['item_id'])) ? $data['item_id'] : 0,
//                            ],
//                            //'value' => (isset($data['data_source_item_id'])) ? $data['data_source_item_id'] : ['id' => 0, 'value' => ''],
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SEARCH,
//                            'name' => 'item_id',
//                            'label' => trans('app.Баннер'),
//                            'css_class' => 'col-sm-6',
//                            'data_url' => 'banners/api/search',
//                            'form_group' => false,
//                            'title' => trans('app.Список баннеров'),
//                            'expression' => 'function(item){
//                             return (["banner::items","banner::tree","banner::item"].indexOf(item.type.id) !== -1)
//                             }',
//                            'filter' => [
//                                'id' => (isset($data['item_id'])) ? $data['item_id'] : 0,
//                            ],
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SELECT,
//                            'name' => 'item_id',
//                            'form_group' => false,
//                            'label' => trans('app.Категория каталога'),
//                            'items' => Category::getCategoryList(true),
//                            'option_group' => false,
//                            'expression' => 'function(item){
//                                return (["catalog::category","catalog::categories"].indexOf(item.type.id) !== -1)
//                            }',
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SEARCH,
//                            'name' => 'item_id',
//                            'label' => trans('app.Элемент каталога'),
//                            'css_class' => 'col-sm-12',
//                            'data_url' => 'catalog/search',
//                            'form_group' => false,
//                            'use_filters' => true,
//                            'title' => trans('app.Элементы каталога'),
//                            'expression' => 'function(item){ return ("catalog::item" == item.type.id) }',
//                            'filter' => [
//                                '_name' => 0,
//                            ],
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SELECT,
//                            'name' => 'item_id',
//                            'form_group' => false,
//                            'label' => trans('app.Категория'),
//                            'items' => ContentCategory::getCategoryList(true),
//                            'option_group' => false,
//                            'expression' => 'function(item){
//                                return (["content::category","content::related"].indexOf(item.type.id) !== -1)
//                            }',
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SEARCH,
//                            'name' => 'item_id',
//                            'label' => trans('app.Материал'),
//                            'css_class' => 'col-sm-12',
//                            'data_url' => 'public/content/search-list',
//                            'form_group' => false,
//                            'use_filters' => true,
//                            'title' => trans('app.Материалы'),
//                            'expression' => 'function(item){ return (["content::item"].indexOf(item.type.id) !== -1) }',
//                            'filter' => [
//                                '_name' => 0,
//                            ],
//                        ],
//                        [
//                            'id' => 'item_id',
//                            'type' => FormFieldTypes::TYPE_SELECT,
//                            'name' => 'item_id',
//                            'form_group' => false,
//                            'label' => trans('app.Форма'),
//                            'items' => FormManager::getList(),
//                            'option_group' => false,
//                            'expression' => 'function(item){
//                                return (["form::item"].indexOf(item.type.id) !== -1)
//                            }',
//                        ],
                    // ],
                    'side' => [
                        [
                            'id' => 'access',
                            'type' => FormFieldTypes::TYPE_ACCESS_LIST,
                            'name' => Components::SITE_ID,
                            'label' => trans('config::forms.components.general.fields.access'),
                            'items' => DomainManager::getAccessDomainList(),
                            'css_class' => 'col-sm-12',
                            'active' => DomainManager::checkIsDefault(),
                        ],
                        [
                            'id' => Components::STATE,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Components::STATE,
                            'label' => trans('config::forms.components.general.fields.state'),
                            'css_class' => 'col-sm-12',
                            'items' => Components::getStatusList(),
                        ],
                        [
                            'id' => Components::CREATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Components::CREATED_AT,
                            'label' => trans('app.Дата создания'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],
                        [
                            'id' => Components::UPDATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Components::UPDATED_AT,
                            'label' => trans('app.Дата обновления'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],
                    ],
                ],
                (object)[
                    'id' => 'catalog-item-media-tab',
                    'name' => trans('app.Медиа материалы'),
                    'active' => false,
                    'fields' => [
                        [
                            'type' => FormFieldTypes::TYPE_COMPONENT_MEDIA,
                        ],
                    ],
                ],
                (object)[
                    'id' => 'catalog-item-extend-tab',
                    'name' => trans('app.Дополнительно'),
                    'fields' => [
                        [
                            'type' => FormFieldTypes::TYPE_COMPONENT_SAMPLE_PROPERTIES,
                            'model_id' => $item->getModelId(),
                            'model' => Components::class,
                        ],
                    ],
                ],
            ],
        ];

        $event->setData($data);
        $event->setResult($result);
    }
}