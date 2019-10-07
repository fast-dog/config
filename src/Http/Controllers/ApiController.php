<?php

namespace FastDog\Config\Http\Controllers;


use FastDog\Config\Events\HelpAdminPrepare;
use FastDog\Config\Models\ConfigHelp;
use FastDog\Core\Form\BaseForm;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\Module;
use FastDog\Core\Models\ModuleManager;
use FastDog\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API
 *
 * @package FastDog\Config\Http\Controllers
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ApiController extends Controller
{

    /**
     * ConfigController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Получение страницы помощи администратору
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHelpPage(Request $request): JsonResponse
    {
        $result = [
            'success' => true,
            'items' => [],
        ];
        /**
         * @var $item ConfigHelp
         */
        $item = ConfigHelp::where([
            ConfigHelp::ALIAS => $request->input('name'),
        ])->first();

        if ($item) {
            $data = $item->getData();
            event(new HelpAdminPrepare($data, $item, $result));
            $data[ConfigHelp::TEXT] .= '<br />' . trans('config::interface.Дата обновления') . ': ' . $item->updated_at->format('d.m.Y H:i');
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Информация о модуле
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @deprecated
     */
    public function getAdminInfo(Request $request): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'items' => [],
        ];

        $this->page_title = trans('config::interface.Администрирование');
        $this->breadcrumbs->push([
            'url' => false,
            'name' => trans('config::interface.Настройки'),
        ]);

        $modules = [];

        Module::orderBy(Module::PRIORITY)->get()->each(function (Module $item) use (&$modules) {
            $item->data = json_decode($item->data);
            array_push($modules, [
                'id' => $item->id,
                Module::NAME => $item->{Module::NAME},
                Module::PRIORITY => $item->{Module::PRIORITY},
                Module::VERSION => $item->{Module::VERSION},
                'description' => $item->data->description,
            ]);
        });
        /** @var ModuleManager $module */
        $moduleManager = \App::make(ModuleManager::class);


        $module = $moduleManager->getInstance('config');

        /**
         * Параметры модуля
         */
        array_push($result['items'], $module->getConfig());

        /**
         * Установленные модули
         */
        array_push($result['items'], $modules);

        /**
         * Список доступа ACL
         */
        array_push($result['items'], []/*Config::getAcl(DomainManager::getSiteId(), strtolower(Config::class))*/);

        return $this->json($result, __METHOD__);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getForm(Request $request): JsonResponse
    {
        $result = ['success' => false, 'items' => []];

        /** @var BaseForm $form */
        $form = BaseForm::where([
            'id' => $request->input('id'),
            BaseForm::USER_ID => 0,
        ])->first();

        $this->page_title = trans('config::interface.Администрирование');
        $this->breadcrumbs->push([
            'url' => false,
            'name' => trans('config::interface.Настройка формы'),
        ]);

        if ($form) {
            array_push($result['items'], $form->getData());
        }

        return $this->json($result, __METHOD__);
    }
}
