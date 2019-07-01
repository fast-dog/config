<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 15.06.2018
 * Time: 10:52
 */

namespace FastDog\Config\Controllers;


use App\Core\Acl\Permission;
use App\Core\Acl\Role;
use App\Core\Module\Module;
use App\Core\Module\ModuleInterface;
use App\Core\Module\ModuleManager;
use App\Http\Controllers\Controller;
use FastDog\Config\Config;
use FastDog\Config\Entity\DomainManager;
use FastDog\Config\Entity\Help;
use FastDog\Config\Events\HelpAdminPrepare;
use App\Modules\Users\Entity\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API
 *
 * @package FastDog\Config\Controllers
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ApiController extends Controller
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
        parent::__construct();

        $this->accessKey = strtolower(Config::class) . '::' . DomainManager::getSiteId() . '::guest';
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
     * Информация о модуле
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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

        $this->page_title = trans('app.Параметры');
        $this->breadcrumbs->push(['url' => '/config/modules', 'name' => trans('app.Настройки')]);

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
            $permission = Permission::where(function (Builder $query) use ($request, $role) {
                $query->where(Permission::NAME, $request->input('permission') . '::' . $role->slug);
            })->first();

            if ($permission) {
                if (isset($permission->slug[$request->input('accessName')])) {
                    $permission_slug = $permission->slug;
                    $permission_slug[$request->input('accessName')] = ($request->input('accessValue') == 'Y') ? true : false;
                    Permission::where('id', $permission->id)->update([
                        'slug' => json_encode($permission_slug),
                    ]);
                }
            }
            $result['acl'] = Config::getAcl(DomainManager::getSiteId(), strtolower(Config::class));
        }

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
}