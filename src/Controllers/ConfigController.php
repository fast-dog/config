<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.12.2016
 * Time: 14:07
 */

namespace FastDog\Config\Controllers;


use App\Core\Acl\Permission;
use App\Core\Acl\Role;
use App\Core\BaseModel;
use App\Core\Module\Module;
use App\Core\Module\ModuleInterface;
use App\Core\Module\ModuleManager;
use App\Core\Module\Components;
use App\Http\Controllers\Controller;
use FastDog\Config\Config;
use FastDog\Config\Entity\Domain;
use FastDog\Config\Entity\DomainManager;
use FastDog\Config\Entity\Emails;
use FastDog\Config\Entity\Help;
use FastDog\Config\Entity\Messages;
use FastDog\Config\Entity\Service;
use FastDog\Config\Entity\Translate;
use FastDog\Config\Events\DomainsItemAdminPrepare;
use FastDog\Config\Events\DomainsItemsAdminPrepare;
use FastDog\Config\Events\HelpAdminPrepare;
use FastDog\Config\Events\MailAdminPrepare;
use FastDog\Config\Events\ComponentsItemAdminPrepare;
use FastDog\Config\Events\ComponentsItemsAdminPrepare;
use FastDog\Config\Events\ServiceAdminPrepare;
use FastDog\Config\Request\AddSiteModule;
use App\Modules\Users\Entity\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Администрирование
 *
 * Администрирование модуля Параметры
 *
 * @package FastDog\Config\Controllers
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 * @deprecated
 */
class ConfigController extends Controller
{
    /**
     * Имя родительского списка доступа
     *
     * из за реализации ACL в пакете kodeine/laravel-acl
     * нужно использовать имя верхнего уровня: action.__CLASS__::SITE_ID::access_level
     *
     *
     * @var string $accessKey
     */
    protected $accessKey = '';

    /**
     * ConfigController constructor.
     */
    public function __construct()
    {
        $this->accessKey = strtolower(Config::class) . '::' . DomainManager::getSiteId() . '::guest';
    }

    /**
     * Список доменов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postWwwItems(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true, 'items' => [], 'cols' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => false, 'name' => trans('app.Домены')],
            ],
            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'filters' => [],
        ];
        $scope = 'default';
        $items = DomainManager::where(function ($query) use ($request, &$scope) {
            $this->_getMenuFilter($query, $request, $scope, DomainManager::class);
        })->$scope()->paginate($request->input('limit', self::PAGE_SIZE));

        $items->each(function ($item) use (&$result) {
            array_push($result['items'], [
                'id' => $item->id,
                DomainManager::NAME => $item->{DomainManager::NAME},
                DomainManager::URL => $item->{DomainManager::URL},
                DomainManager::CODE => $item->{DomainManager::CODE},
                DomainManager::DATA => json_decode($item->{DomainManager::DATA}),
                'link' => '/config/www/' . $item->id,
            ]);
        });

        $this->_getCurrentPaginationInfo($request, $items, $result);
        \Event::fire(new DomainsItemsAdminPrepare($result, $items));

        return $this->json($result, __METHOD__);
    }

    /**
     * Редактирование домена
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWwwItem(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/modules', 'name' => trans('app.Домены')],
            ],
            'status' => DomainManager::getStatusList(),
        ];


        $id = \Route::input('id', null);
        $item = null;
        if ($id === 0) {

        } else {
            /**
             * @var $item Domain
             */
            $item = DomainManager::find($id);
        }
        if ($item) {
            $data = $item->getData();

            \Event::fire(new DomainsItemAdminPrepare($data, $item, $result));

            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Изменение доступа ACL
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAccess(Request $request)
    {
        $result = ['success' => false];

        $role = Role::where([
            Role::NAME => $request->input('role'),
        ])->first();
        if ($role) {
            $permission = Permission::where(function ($query) use ($request, $role) {
                $query->where(Permission::NAME, $request->input('permission') . '::' . $role->slug);
            })->first();

            if ($permission) {
                if (isset($permission->slug[$request->input('accessName')])) {
                    $permission_slug = $permission->slug;
                    $permission_slug[$request->input('accessName')] = ($request->input('accessValue') == 'Y') ? true : false;
                    Permission::where('id', $permission->id)->update([
                        'slug' => \GuzzleHttp\json_encode($permission_slug),
                    ]);
                }
            }
            $result['acl'] = Config::getAcl($role->{Role::SITE_ID});
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление модулей
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSiteModulesSelfUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), Components::class);

        return $this->json($result, __METHOD__);
    }

    /**
     * Список модулей
     *
     * Список публикуемых на сайте модулей
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postPublicModules(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/modules', 'name' => trans('app.Публикуемые модули')],
            ],
            'cols' => [
                [
                    'name' => trans('app.Название'),
                    'key' => Components::NAME,
                    'domain' => true,
                    'extra' => true,
                ],
                [
                    'name' => trans('app.Дата'),
                    'key' => 'created_at',
                    'width' => 150,
                    'link' => null,
                    'class' => 'text-center',
                ],
            ],
            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
        ];
        $scope = 'default';
        /**
         * @var $items LengthAwarePaginator
         */
        $items = Components::where(function ($query) use ($request, &$scope) {
            $this->_getMenuFilter($query, $request, $scope, Components::class);
        })->$scope()->paginate($request->input('limit', self::PAGE_SIZE));

        foreach ($items as $item) {
            $data = [
                'id' => $item->id,
                Components::NAME => $item->{Components::NAME},
                Components::STATE => $item->{Components::STATE},
                Components::SITE_ID => $item->{Components::SITE_ID},
                'published' => $item->{Components::STATE},
                'created_at' => $item->created_at->format('d.m.Y'),
                'link' => '/config/module/' . $item->id,
                'data' => $item->{Components::DATA},
                'checked' => false,
            ];
            \Event::fire(new ComponentsItemsAdminPrepare($data));
            array_push($result['items'], $data);
        }

        $this->_getCurrentPaginationInfo($request, $items, $result);

        return $this->json($result, __METHOD__);
    }

    /**
     * Информация о модуле
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminInfo(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/modules', 'name' => trans('app.Настройки')],
            ],

            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
        ];

        $items = Module::orderBy(Module::PRIORITY)->get();
        $modules = [];
        foreach ($items as $item) {
            $item->data = \GuzzleHttp\json_decode($item->data);

            array_push($modules, [
                'id' => $item->id,
                Module::NAME => $item->{Module::NAME},
                Module::PRIORITY => $item->{Module::PRIORITY},
                Module::VERSION => $item->{Module::VERSION},
                'description' => $item->data->description,
            ]);
        }
        $moduleManager = \App::make(ModuleManager::class);
        /**
         * @var $moduleManager ModuleManager
         */
        $module = $moduleManager->getInstance('FastDog\Config\Config');
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
        array_push($result['items'], Config::getAcl(DomainManager::getSiteId(), strtolower(Config::class)));

        return $this->json($result, __METHOD__);
    }

    /**
     * Список публичных модулей
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicModule(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/modules', 'name' => trans('app.Публикуемые модули')],
            ],
            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
            'modules' => Components::getInstallModules(),
            'status' => Components::getStatusList(),
            'allow_access' => DomainManager::checkIsDefault(),
        ];

        if (DomainManager::checkIsDefault()) {
            $result['access_list'] = DomainManager::getAccessDomainList();
        }
        $item = null;
        if ($id = \Route::input('id')) {
            $item = Components::find($id);
        }
        if ($item === null) {
            $item = new Components();
        }

        $data = $item->getData();

        if ($data[Components::NAME]) {
            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Components::NAME]]);
        }

        \Event::fire(new ComponentsItemAdminPrepare($data, $item, $result));

        array_push($result['items'], $data);


        return $this->json($result, __METHOD__);
    }

    /**
     * Действия с модулями
     *
     * Выполнение команд: переустановка, сброс ACL
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postModuleCmd(Request $request)
    {
        $result = ['success' => true,];

        $module = Module::find($request->input('id'));
        if ($module) {
            $module->{Module::DATA} = json_decode($module->{Module::DATA});

            /**
             * @var $moduleManager ModuleManager
             */
            $moduleManager = \App::make(ModuleManager::class);
            /**
             * @var $instance ModuleInterface
             */
            $instance = $moduleManager->getInstance($module->{Module::DATA}->{'source'}->{'class'});
            if ($instance) {
                switch ($request->input('cmd')) {
                    case 'reload_acl':
                        $instance->initAcl();
                        break;
                    case 'reinstall':
                        $dataFile = $instance->getModuleDir() . DIRECTORY_SEPARATOR . 'module.json';
                        $_module = json_decode(file_get_contents($dataFile));

                        Module::where('id', $module->id)->update([
                            Module::NAME => $_module->{Module::NAME},
                            Module::VERSION => $_module->{Module::VERSION},
                            Module::PRIORITY => $_module->{Module::PRIORITY},
                            Module::DATA => json_encode($_module),
                        ]);

                        break;
                }
            }
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление публичного модуля
     *
     * @param AddSiteModule $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postModuleUpdate(AddSiteModule $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/modules', 'name' => trans('app.Публикуемые модули')],
            ],

            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
            'modules' => Components::getInstallModules(),
            'status' => Components::getStatusList(),
            'allow_access' => DomainManager::checkIsDefault(),
        ];

        if (DomainManager::checkIsDefault()) {
            $result['access_list'] = DomainManager::getAccessDomainList();
        }

        $data = [
            Components::NAME => $request->input(Components::NAME),
            Components::STATE => $request->input(Components::STATE, Components::STATE_PUBLISHED),
            Components::SITE_ID => $request->input(Components::SITE_ID),
            Components::DATA => json_encode($request->input(Components::DATA)),
        ];
        if ($data[Components::STATE] === null) {
            $data[Components::STATE] = Components::STATE_PUBLISHED;
        }
        if ($request->input('id') !== null) {
            $item = Components::find($request->input('id'));
            Components::where('id', $item->id)->update($data);
        } else {
            $item = Components::create($data);
            $request->merge([
                'id' => $item->id,
            ]);
        }
        $item = Components::find($request->input('id'));

        if ($item) {
            $item = Components::find($item->id);
            $data = $item->getData();

            \Event::fire(new ComponentsItemAdminPrepare($data, $item, $result));

            array_push($result['items'], $data);

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Components::NAME]]);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Список email шаблонов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMails(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();


        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => false, 'name' => trans('app.Почтовые события')],
            ],
            'cols' => [
                [
                    'name' => trans('app.Название'),
                    'key' => Components::NAME,
                    'domain' => true,
                    'extra' => true,
                ],
                [
                    'name' => trans('app.Дата'),
                    'key' => 'created_at',
                    'width' => 150,
                    'link' => null,
                    'class' => 'text-center',
                ],
            ],
            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
            'modules' => Components::getInstallModules(),
            'status' => Components::getStatusList(),
            'allow_access' => DomainManager::checkIsDefault(),
        ];
        $scope = 'default';
        $items = Emails::where(function ($query) use ($request, &$scope) {
            $this->_getMenuFilter($query, $request, $scope, Emails::class);
        })->$scope()->paginate($request->input('limit', self::PAGE_SIZE));

        foreach ($items as $item) {
            $data = $item->getData();
            $data['created_at'] = $item->created_at->format('d.m.Y');
            $data['published'] = $data[Emails::STATE];
            $data['link'] = '/config/mail/' . $item->id;
            array_push($result['items'], $data);
        }

        $this->_getCurrentPaginationInfo($request, $items, $result);

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление модели email шаблонов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSystemMailsSelfUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), Emails::class);

        return $this->json($result, __METHOD__);
    }

    /**
     * Редактирование email шаблона
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemMail(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/mails', 'name' => trans('app.Почтовые события')],
            ],
            'status' => DomainManager::getStatusList(),
        ];


        $id = \Route::input('id', null);
        /**
         * @var $item Emails
         */
        $item = new Emails();

        if ($id > 0) {
            $item = Emails::find($id);
        }

        if ($item) {
            $data = $item->getData();
            \Event::fire(new MailAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Emails::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление email шаблонов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSystemMailsSave(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/mails', 'name' => trans('app.Почтовые события')],
            ],
            'status' => DomainManager::getStatusList(),
        ];
        $item = null;
        $data = [
            Emails::NAME => $request->input(Emails::NAME),
            Emails::ALIAS => $request->input(Emails::ALIAS),
            Emails::TEXT => $request->input(Emails::TEXT),
            Emails::DATA => json_encode($request->input(Emails::DATA)),
        ];
        if ($request->input('id') > 0) {
            $item = Emails::find($request->input('id'));
            if ($item) {
                Emails::where('id', $item->id)->update($data);
                $item = Emails::find($item->id);
            }
        } else {
            $item = Emails::create($data);
        }

        if ($item) {
            $data = $item->getData();

            \Event::fire(new MailAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Emails::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Список шаблонов личных сообщений
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMessages(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => false, 'name' => trans('app.Личные сообщения')],
            ],
            'cols' => [
                [
                    'name' => trans('app.Название'),
                    'key' => Components::NAME,
                    'domain' => true,
                    'extra' => true,
                ],
                [
                    'name' => trans('app.Дата'),
                    'key' => 'created_at',
                    'width' => 150,
                    'link' => null,
                    'class' => 'text-center',
                ],
            ],
            'access' => [
                'reorder' => $user->can('reorder.' . $this->accessKey),
                'delete' => $user->can('delete.' . $this->accessKey),
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
            'modules' => Components::getInstallModules(),
            'status' => Components::getStatusList(),
            'allow_access' => DomainManager::checkIsDefault(),
        ];
        $scope = 'default';
        $items = Messages::where(function ($query) use ($request, &$scope) {
            $this->_getMenuFilter($query, $request, $scope, Messages::class);
        })->$scope()->paginate($request->input('limit', self::PAGE_SIZE));

        foreach ($items as $item) {
            $data = $item->getData();
            $data['created_at'] = $item->created_at->format('d.m.Y');
            $data['published'] = $data[Messages::STATE];
            $data['link'] = '/config/message/' . $item->id;
            array_push($result['items'], $data);
        }

        $this->_getCurrentPaginationInfo($request, $items, $result);

        return $this->json($result, __METHOD__);
    }

    /**
     * Редактирование шаблона личного сообщения
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessage(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/messages', 'name' => trans('app.Личные сообщения')],
            ],
            'status' => DomainManager::getStatusList(),
        ];


        $id = \Route::input('id', null);
        /**
         * @var $item Messages
         */
        $item = new Messages();

        if ($id > 0) {
            $item = Messages::find($id);
        }

        if ($item) {
            $data = $item->getData();
            \Event::fire(new MailAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Messages::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление личного сообщения шаблонов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMessageSave(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/messages', 'name' => trans('app.Личные сообщения')],
            ],
            'status' => DomainManager::getStatusList(),
        ];
        $item = null;
        $data = [
            Messages::NAME => $request->input(Messages::NAME),
            Messages::ALIAS => $request->input(Messages::ALIAS),
            Messages::TEXT => $request->input(Messages::TEXT),
            Messages::DATA => json_encode($request->input(Messages::DATA)),
        ];
        if ($request->input('id') > 0) {
            $item = Messages::find($request->input('id'));
            if ($item) {
                Messages::where('id', $item->id)->update($data);
                $item = Messages::find($item->id);
            }
        } else {
            $item = Messages::create($data);
        }

        if ($item) {
            $data = $item->getData();

            \Event::fire(new MailAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Messages::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Список терминов локализации
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postTranslates(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => false, 'name' => trans('app.Локализация')],
            ],
            'cols' => [
                [
                    'name' => trans('app.Термин для перевода'),
                    'key' => Translate::KEY,
                    'domain' => true,
                    'extra' => true,
                ],
                [
                    'name' => '#',
                    'key' => 'id',
                    'width' => 150,
                    'link' => null,
                    'class' => 'text-center',
                ],
            ],
            'access' => [
                'reorder' => false,
                'delete' => false,
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
        ];
        $scope = 'default';
        $items = Translate::where(function ($query) use ($request, &$scope) {
            $filter = $request->input('filter', []);
            foreach ($filter as $name => $value) {
                if ($value !== '')
                    switch ($name) {
                        case 'title':
                            $query->where(Translate::KEY, 'LIKE', trim($value) . '%');
                            break;
                    }
            }
            $query->where(Translate::CODE, 'public');
        })->paginate($request->input('limit', self::PAGE_SIZE));
        /**
         * @var $item Translate
         */
        foreach ($items as $item) {
            array_push($result['items'], [
                'id' => $item->id,
                Translate::KEY => $item->{Translate::KEY},
                'link' => '/config/translate/' . $item->id,
            ]);
        }

        $this->_getCurrentPaginationInfo($request, $items, $result);

        return $this->json($result, __METHOD__);
    }

    /**
     * Редактирование шаблона личного сообщения
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTranslate(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/translate', 'name' => trans('app.Локализация')],
            ],
        ];

        $id = \Route::input('id', null);
        /**
         * @var $item Translate
         */
        $item = new Translate();

        if ($id > 0) {
            $item = Translate::find($id);
        }

        if ($item) {
            array_push($result['breadcrumbs'], ['url' => false, 'name' => $item->{Translate::KEY}]);
            array_push($result['items'], [
                'id' => $item->id,
                BaseModel::NAME => $item->{Translate::KEY},
                BaseModel::ALIAS => $item->{Translate::VALUE},
            ]);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление личного сообщения шаблонов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postTranslateSave(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/translate', 'name' => trans('app.Локализация')],
            ],
        ];
        $item = null;
        $data = [
            Translate::KEY => $request->input(Messages::NAME),
            Translate::VALUE => $request->input(Messages::ALIAS),
        ];
        if ($request->input('id') > 0) {
            $item = Translate::find($request->input('id'));
            if ($item) {
                Translate::where('id', $item->id)->update($data);
                $item = Translate::find($item->id);
            }
        } else {
            $item = Translate::create($data);
        }

        if (config('cache.default') == 'redis') {
            \Cache::tags(['core-translate'])->flush();
        }

        if ($item) {
            array_push($result['breadcrumbs'], ['url' => false, 'name' => $item->{Translate::KEY}]);
            array_push($result['items'], [
                'id' => $item->id,
                BaseModel::NAME => $item->{Translate::KEY},
                BaseModel::ALIAS => $item->{Translate::VALUE},
            ]);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление сообщений из списка
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMessageSelfUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), Messages::class);

        return $this->json($result, __METHOD__);
    }


    /**
     * Список платных сервисов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postServices(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => false, 'name' => trans('app.Сервисы')],
            ],
            'cols' => [
                [
                    'name' => trans('app.Название'),
                    'key' => Service::NAME,
                    'domain' => true,
                    'extra' => true,
                ],
                [
                    'name' => '#',
                    'key' => 'id',
                    'width' => 150,
                    'link' => null,
                    'class' => 'text-center',
                ],
            ],
            'access' => [
                'reorder' => false,
                'delete' => false,
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
        ];
        $scope = 'default';
        $items = Service::where(function ($query) use ($request, &$scope) {
            $filter = $request->input('filter', []);
            foreach ($filter as $name => $value) {
                if ($value !== '')
                    switch ($name) {
                        case 'title':
                            $query->where(Translate::KEY, 'LIKE', trim($value) . '%');
                            break;
                    }
            }
        })->paginate($request->input('limit', self::PAGE_SIZE));
        /**
         * @var $item Translate
         */
        foreach ($items as $item) {
            array_push($result['items'], [
                'id' => $item->id,
                Service::NAME => $item->{Service::NAME},
                'link' => '/config/service/' . $item->id,
            ]);
        }

        $this->_getCurrentPaginationInfo($request, $items, $result);

        return $this->json($result, __METHOD__);
    }

    /**
     * Редактирование шаблона личного сообщения
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getService(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/translate', 'name' => trans('app.Сервисы')],
            ],
        ];

        $id = \Route::input('id', null);
        /**
         * @var $item Service
         */
        $item = new Service();

        if ($id > 0) {
            $item = Service::find($id);
        }

        if ($item) {
            array_push($result['breadcrumbs'], ['url' => false, 'name' => $item->{Service::NAME}]);
            $data = $item->getData();
            \Event::fire(new ServiceAdminPrepare($data, $item, $result));

            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление сервисов
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postServiceSave(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/mails', 'name' => trans('app.Сервисы')],
            ],
            'status' => DomainManager::getStatusList(),
        ];
        $item = null;
        $data = [
            Service::NAME => $request->input(Service::NAME),
            Service::INTROTEXT => $request->input(Service::INTROTEXT),
            Service::FULLTEXT => $request->input(Service::FULLTEXT),
            Service::DATA => json_encode($request->input(Service::DATA)),
        ];
        if ($request->input('id') > 0) {
            /**
             * @var $item Service
             */
            $item = Service::find($request->input('id'));
            if ($item) {
                Service::where('id', $item->id)->update($data);
                $item = Service::find($item->id);
            }
        } else {
            $item = Service::create($data);
        }

        if ($item) {
            $data = $item->getData();

            \Event::fire(new ServiceAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Service::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Список страниц помощи администраторам
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postHelps(Request $request)
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        $result = [
            'success' => true,
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/help', 'name' => trans('app.Помощь администраторам')],
            ],
            'cols' => [
                [
                    'name' => trans('app.Название'),
                    'key' => Service::NAME,
                    'domain' => true,
                    'extra' => true,
                ],
                [
                    'name' => '#',
                    'key' => 'id',
                    'width' => 150,
                    'link' => null,
                    'class' => 'text-center',
                ],
            ],
            'access' => [
                'reorder' => false,
                'delete' => false,
                'update' => $user->can('update.' . $this->accessKey),
                'create' => $user->can('create.' . $this->accessKey),
            ],
            'items' => [],
        ];

        $scope = 'default';
        $items = Help::where(function ($query) use ($request, &$scope) {
            $filter = $request->input('filter', []);
            foreach ($filter as $name => $value) {
                if ($value !== '')
                    switch ($name) {
                        case 'title':
                            $query->where(Help::NAME, 'LIKE', trim($value) . '%');
                            break;
                    }
            }
        })->paginate($request->input('limit', self::PAGE_SIZE));
        /**
         * @var $item Help
         */

        foreach ($items as $item) {
            $data = $item->getData();
            $data['created_at'] = $item->created_at->format('d.m.Y');
            $data['published'] = $data[Messages::STATE];
            $data['link'] = '/config/help/' . $item->id;
            array_push($result['items'], $data);
        }

        $this->_getCurrentPaginationInfo($request, $items, $result);


        return $this->json($result, __METHOD__);
    }

    /**
     * Редактирование страницы помощи администраторам
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHelp(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/help', 'name' => trans('app.Помощь администраторам')],
            ],
            'status' => DomainManager::getStatusList(),
        ];


        $id = \Route::input('id', null);
        /**
         * @var $item Help
         */
        $item = new Help();

        if ($id > 0) {
            $item = Help::find($id);
        }

        if ($item) {
            $data = $item->getData();

            \Event::fire(new HelpAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Help::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Получение страницы помощи администратору
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHelpPage(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
        ];
        /**
         * @var $item Help
         */
        $item = Help::where([
            Help::ALIAS => $request->input('name'),
        ])->first();

        if ($item) {
            $data = $item->getData();
            \Event::fire(new HelpAdminPrepare($data, $item, $result));
            $data[Help::TEXT] .= '<br />' . trans('app.Дата обновления') . ': ' . $item->updated_at->format('d.m.Y H:i');
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление страницы помощи администраторам
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postHelpSave(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => trans('app.Параметры'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => '/config/help', 'name' => trans('app.Помощь администраторам')],
            ],
        ];
        $item = null;
        $data = [
            Help::NAME => $request->input(Help::NAME),
            Help::ALIAS => $request->input(Help::ALIAS),
            Help::STATE => $request->input(Help::STATE, Help::STATE_PUBLISHED),
            Help::TEXT => $request->input(Help::TEXT),
            Help::DATA => json_encode($request->input(Help::DATA)),
        ];
        if ($request->input('id') > 0) {
            $item = Help::find($request->input('id'));
            if ($item) {
                Help::where('id', $item->id)->update($data);
                $item = Help::find($item->id);
            }
        } else {
            $item = Help::create($data);
        }

        if ($item) {
            $data = $item->getData();

            \Event::fire(new HelpAdminPrepare($data, $item, $result));

            array_push($result['breadcrumbs'], ['url' => false, 'name' => $data[Help::NAME]]);
            array_push($result['items'], $data);
        }

        return $this->json($result, __METHOD__);
    }

}