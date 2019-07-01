<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.12.2016
 * Time: 2:57
 */

namespace FastDog\Config;


use App\Core\Module\Components;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Параметры скрипта
 *
 * @package FastDog\Config
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Config
{

    /**
     * Идентификатор модуля
     *
     * @const string
     */
    const MODULE_ID = 'config';

    /**
     * Параметры конфигурации описанные в module.json
     *
     * @var null|object $config
     */
    protected $config;

    /**
     * Устанавливает параметры в контексте объекта
     *
     * @param $data
     * @return mixed
     */
    public function setConfig($data)
    {
        $this->config = $data;
    }

    /**
     *  Возвращает параметры объекта
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Возвращает доступные шаблоны
     *
     * @param $paths
     * @return null|array
     */
    public function getTemplates($paths = '')
    {
        return null;
    }

    /**
     * Возвращает доступные типы меню
     *
     * @return null|array
     */
    public function getMenuType()
    {
        return [];
    }

    /**
     * Возвращает информацию о модуле
     *
     * @param bool $includeTemplates
     * @return array
     */
    public function getModuleInfo($includeTemplates = true)
    {
        $paths = Arr::first(\Config::get('view.paths'));
        $templates_paths = $this->getTemplatesPaths();

        return [
            'id' => self::MODULE_ID,
            'menu' => function () use ($paths, $templates_paths) {
                $result = [];
                foreach ($this->getMenuType() as $id => $item) {
                    array_push($result, [
                        'id' => $id,
                        'name' => $item,
                        'templates' => (isset($templates_paths[$id])) ? $this->getTemplates($paths . $templates_paths[$id]) : [],
                        'class' => __CLASS__,
                    ]);
                }

                return $result;
            },
            'templates_paths' => $templates_paths,
            'module_type' => $this->getMenuType(),
            'admin_menu' => function () {
                return $this->getAdminMenuItems();
            },
            'access' => function () {
                return [
                    '000',
                ];
            },
        ];
    }

    /**
     * @return array
     */
    public function getTemplatesPaths(): array
    {
        return [];
    }

    /**
     * Возвращает возможные типы модулей
     *
     * @return mixed
     */
    public function getModuleType()
    {
        return [];
    }

    /**
     * Возвращает маршрут компонента
     *
     * @param Request $request
     * @param Menu $item
     * @return mixed
     */
    public function getMenuRoute($request, $item)
    {
        return [];
    }


    /**
     * Публичный контент
     *
     * Метод возвращает отображаемый в публичной части контнет
     *
     * @param Components $module
     * @return null|string
     */
    public function getContent(Components $module)
    {
        return null;
    }

    /**
     * Метод возвращает директорию модуля
     *
     * @return string
     */
    public function getModuleDir()
    {
        return dirname(__FILE__);
    }

    /**
     * Рабочий стол
     *
     * Возвращает параметры блоков добавляемых на рабочий стол администратора
     *
     * @return array
     */
    public function getDesktopWidget()
    {
        return [];
    }


    /**
     * Меню администратора
     *
     * Возвращает пунты меню для раздела администратора
     *
     * @return array
     */
    public function getAdminMenuItems()
    {
        $result = [
            'icon' => 'fa-gears',
            'name' => trans('config::interface.Настройки'),
            'route' => '/configuration',
            'children' => [],
        ];

        array_push($result['children'], [
            'icon' => 'fa-globe',
            'name' => trans('user::interface.Домены'),
            'route' => '/configuration/domain',
        ]);

        array_push($result['children'], [
            'icon' => 'fa-cubes',
            'name' => trans('user::interface.Компоненты'),
            'route' => '/configuration/components',
        ]);

        array_push($result['children'], [
            'icon' => 'fa-envelope',
            'name' => trans('user::interface.Почтовые события'),
            'route' => '/configuration/emails',
        ]);

        array_push($result['children'], [
            'icon' => 'fa-language',
            'name' => trans('user::interface.Локализация'),
            'route' => '/configuration/localization',
        ]);

        array_push($result['children'], [
            'icon' => 'fa-life-bouy',
            'name' => trans('user::interface.Помощь'),
            'route' => '/configuration/help',
        ]);

        array_push($result['children'], [
            'icon' => 'fa-gears',
            'name' => trans('user::interface.Параметры'),
            'route' => '/configuration/parameters',
        ]);

        return $result;
    }

    /**
     * Возвращает массив таблиц для резервного копирования
     *
     * @return array
     */
    public function getTables()
    {
        // TODO: Implement getTables() method.
    }
}