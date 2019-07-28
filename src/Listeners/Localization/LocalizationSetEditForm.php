<?php

namespace FastDog\Config\Listeners\Localization;


use FastDog\Config\Events\Localization\LocalizationAdminPrepare as LocalizationAdminPreparePrepareEvent;
use FastDog\Config\Models\Translate;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Models\FormFieldTypes;
use Illuminate\Http\Request;

/**
 * Локализация - форма редактирования
 *
 * @package FastDog\Config\Listeners\Localization
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class LocalizationSetEditForm
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
     * @param LocalizationAdminPreparePrepareEvent $event
     */
    public function handle(LocalizationAdminPreparePrepareEvent $event)
    {
        /**
         * @var $item Translate
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }


        $result['form'] = [
            'create_url' => 'config/localization/save',
            'update_url' => 'config/localization/save',
            'help' => 'help_localization',
            'tabs' => (array)[
                (object)[
                    'id' => 'catalog-item-general-tab',
                    'name' => trans('config::forms.localization.general.title'),
                    'active' => true,
                    'fields' => (array)[
                        [
                            'id' => Translate::CODE,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Translate::CODE,
                            'label' => trans('config::forms.localization.general.fields.code'),
                            'css_class' => 'col-sm-12',
                            'form_group' => false,
                            'readonly' => $this->request->has('id'),
                        ],
                        [
                            'id' => Translate::KEY,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Translate::KEY,
                            'label' => trans('config::forms.localization.general.fields.name'),
                            'css_class' => 'col-sm-12',
                            'form_group' => false,
                            'readonly' => $this->request->has('id'),
                        ],
                        [
                            'id' => Translate::VALUE,
                            'type' => FormFieldTypes::TYPE_TEXT,
                            'name' => Translate::VALUE,
                            'label' => trans('config::forms.localization.general.fields.value'),
                            'css_class' => 'col-sm-12',
                            'form_group' => false,
                            'required' => $this->request->has('id'),
                        ],
                    ],
                    'side' => [
                        [
                            'id' => Translate::SITE_ID,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Translate::SITE_ID,
                            'label' => trans('config::forms.localization.general.fields.access'),
                            'items' => DomainManager::getAccessDomainList(),
                            'css_class' => 'col-sm-12',
                            'active' => DomainManager::checkIsDefault(),
                        ],
                        [
                            'id' => Translate::STATE,
                            'type' => FormFieldTypes::TYPE_SELECT,
                            'name' => Translate::STATE,
                            'label' => trans('config::forms.localization.general.fields.state'),
                            'css_class' => 'col-sm-12',
                            'items' => Translate::getStatusList(),
                        ],
                    ],
                ],
            ],
        ];

        $event->setData($data);
        $event->setResult($result);
    }
}
