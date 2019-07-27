<?php

namespace FastDog\Config\Listeners;


use FastDog\Config\Events\MailAdminPrepare as MailAdminPrepareEvent;
use FastDog\Config\Models\Emails;
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
class EmailsItemSetEditForm
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
     * @param MailAdminPrepareEvent $event
     */
    public function handle(MailAdminPrepareEvent $event)
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
            'create_url' => 'config/emails/add',
            'update_url' => 'config/emails/save',
            'help' => 'help_item',
            'tabs' => (array)[
                (object)[
                    'id' => 'catalog-item-general-tab',
                    'name' => trans('config::forms.email.general.title'),
                    'active' => true,
                    'fields' => (array)[
                        [
                            'id' => Emails::NAME,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Emails::NAME,
                            'label' => trans('config::forms.email.general.fields.name'),
                            'css_class' => 'col-sm-6',
                            'form_group' => false,
                            'required' => true,
                            'validate' => 'required|min:5',
                        ],
                        [
                            'id' => Emails::ALIAS,
                            'type' => FormFieldTypes::TYPE_TEXT_ALIAS,
                            'name' => Emails::ALIAS,
                            'label' => trans('config::forms.email.general.fields.alias'),
                            'css_class' => 'col-sm-6',
                            'required' => true,
                            'form_group' => false,
                        ],
                        [
                            'id' => Emails::TEXT,
                            'type' => FormFieldTypes::TYPE_CODE_EDITOR,
                            'name' => Emails::TEXT,
                            'label' => trans('config::forms.email.general.fields.html'),
                            'css_class' => 'col-sm-12',
                            'required' => true,
                            'form_group' => false,
                        ],

                    ],
                    'side' => [
                        [
                            'id' => 'access',
                            'type' => FormFieldTypes::TYPE_ACCESS_LIST,
                            'name' => Emails::SITE_ID,
                            'label' => trans('config::forms.email.general.fields.access'),
                            'items' => DomainManager::getAccessDomainList(),
                            'css_class' => 'col-sm-12',
                            'active' => DomainManager::checkIsDefault(),
                        ],
                        [
                            'id' => Emails::STATE,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Emails::STATE,
                            'label' => trans('config::forms.email.general.fields.state'),
                            'css_class' => 'col-sm-12',
                            'items' => Emails::getStatusList(),
                        ],
                        [
                            'id' => Emails::CREATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Emails::CREATED_AT,
                            'label' => trans('config::forms.email.general.fields.created_at'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],
                        [
                            'id' => Emails::UPDATED_AT,
                            'type' => FormFieldTypes::TYPE_DATE,
                            'name' => Emails::UPDATED_AT,
                            'label' => trans('config::forms.email.general.fields.updated_at'),
                            'css_class' => 'col-sm-12',
                            'form_group' => true,
                            'readonly' => true,
                        ],

                    ],
                ],
                (object)[
                    'id' => 'catalog-item-extend-tab',
                    'name' => trans('config::forms.email.extend.title'),
                    'fields' => [
                        [
                            'type' => FormFieldTypes::TYPE_COMPONENT_SAMPLE_PROPERTIES,
                            'model_id' => $item->getModelId(),
                            'model' => Emails::class,
                        ],
                    ],
                ],
            ],
        ];

        $event->setData($data);
        $event->setResult($result);
    }

}
