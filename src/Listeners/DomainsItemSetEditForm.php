<?php
namespace FastDog\Config\Listeners;

use FastDog\Config\Events\DomainsItemAdminPrepare as DomainsItemAdminPrepareEvent;
use FastDog\Core\Models\Domain;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Models\FormFieldTypes;
use Illuminate\Http\Request;

/**
 * Редактирование домена
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainsItemSetEditForm
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
     * @param DomainsItemAdminPrepareEvent $event
     */
    public function handle(DomainsItemAdminPrepareEvent $event)
    {
        /**
         * @var $item Domain
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }


        $result['form'] = [
            'create_url' => 'config/domain/add',
            'update_url' => 'config/domain/save',
            'help' => 'domain_item',
            'tabs' => (array)[
                (object)[
                    'id' => 'catalog-item-general-tab',
                    'name' => trans('app.Основная информация'),
                    'active' => true,
                    'fields' => (array)[
                        [
                            'id' => Domain::NAME,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Domain::NAME,
                            'label' => trans('app.Название'),
                            'css_class' => 'col-sm-6',
                            'form_group' => false,
                            'required' => true,
                            'validate' => 'required|min:5',
                        ],
                        [
                            'id' => Domain::URL,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Domain::URL,
                            'label' => trans('app.Адрес сайта (URL)'),
                            'css_class' => 'col-sm-6',
                            'required' => true,
                            'form_group' => false,
                        ],
                        [
                            'id' => Domain::CODE,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Domain::CODE,
                            'label' => trans('app.Идетнификатор домена'),
                            'css_class' => 'col-sm-6',
                            'required' => true,
                            'form_group' => false,
                        ],
                        [
                            'id' => Domain::SITE_ID,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Domain::SITE_ID,
                            'label' => trans('app.Код сайта'),
                            'css_class' => 'col-sm-6',
                            'items' => DomainManager::getAccessDomainList(),
                            'form_group' => false,
                        ],
                        [
                            'id' => Domain::LANG,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Domain::LANG,
                            'label' => trans('app.Локализация'),
                            'css_class' => 'col-sm-6',
                            'items' => DomainManager::getAllowLang(),
                            'required' => true,
                            'form_group' => false,
                        ],
                    ],
                    'side' => [
                        [
                            'id' => Domain::STATE,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Domain::STATE,
                            'label' => trans('app.Состояние'),
                            'css_class' => 'col-sm-12',
                            'items' => Domain::getStatusList(),
                        ],
                        [
                            'id' => Domain::CREATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Domain::CREATED_AT,
                            'label' => trans('app.Дата создания'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],
                        [
                            'id' => Domain::UPDATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Domain::UPDATED_AT,
                            'label' => trans('app.Дата обновления'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
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
                            'model' => Domain::class,
                        ],
                    ],
                ],
            ],
        ];

        $event->setData($data);
        $event->setResult($result);
    }

}