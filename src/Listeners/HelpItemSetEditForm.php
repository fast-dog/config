<?php

namespace FastDog\Config\Listeners;

use FastDog\Config\Events\HelpAdminPrepare as HelpAdminPrepareEvent;
use FastDog\Config\Models\Emails;
use FastDog\Core\Models\FormFieldTypes;
use Illuminate\Http\Request;

/**
 * Редактирование домена
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class HelpItemSetEditForm
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
     * @param HelpAdminPrepareEvent $event
     */
    public function handle(HelpAdminPrepareEvent $event)
    {
        /**
         * @var $item Emails
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }


        $result['form'] = [
            'create_url' => 'config/help/save',
            'update_url' => 'config/help/save',
            'help' => 'help_item',
            'tabs' => (array)[
                (object)[
                    'id' => 'catalog-item-general-tab',
                    'name' => trans('app.Основная информация'),
                    'active' => true,
                    'fields' => (array)[
                        [
                            'id' => Emails::NAME,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Emails::NAME,
                            'label' => trans('app.Название'),
                            'css_class' => 'col-sm-6',
                            'form_group' => false,
                            'required' => true,
                            'validate' => 'required|min:5',
                        ],
                        [
                            'id' => Emails::ALIAS,
                            'type' => FormFieldTypes::TYPE_TEXT_ALIAS,
                            'name' => Emails::ALIAS,
                            'label' => trans('app.Псевдоним'),
                            'css_class' => 'col-sm-6',
                            'required' => true,
                            'form_group' => false,
                        ],
                        [
                            'id' => Emails::TEXT,
                            'type' => FormFieldTypes::TYPE_CODE_EDITOR,
                            'name' => Emails::TEXT,
                            'label' => trans('app.HTML текст'),
                            'css_class' => 'col-sm-12',
                            'required' => true,
                            'form_group' => false,
                        ],

                    ],
                    'side' => [
                        [
                            'id' => Emails::STATE,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Emails::STATE,
                            'label' => trans('app.Состояние'),
                            'css_class' => 'col-sm-12',
                            'items' => Emails::getStatusList(),
                        ],
                        [
                            'id' => Emails::CREATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Emails::CREATED_AT,
                            'label' => trans('app.Дата создания'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],
                        [
                            'id' => Emails::UPDATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Emails::UPDATED_AT,
                            'label' => trans('app.Дата обновления'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],

                    ],
                ],
            ],
        ];

        $event->setData($data);
        $event->setResult($result);
    }

}