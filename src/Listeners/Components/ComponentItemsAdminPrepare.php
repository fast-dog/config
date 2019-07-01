<?php
namespace FastDog\Config\Listeners\Components;


use App\Core\Module\Components;
use FastDog\Config\Events\Components\ComponentItemsAdminPrepare as PublicModulesItemsAdminPrepareEvent;
use Illuminate\Http\Request;

/**
 * Список публичных моудлей
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemsAdminPrepare
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
     * @param PublicModulesItemsAdminPrepareEvent $event
     * @deprecated
     */
    public function handle(PublicModulesItemsAdminPrepareEvent $event)
    {
        $data = $event->getData();

        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }

        $data['type'] = ['name' => 'не определено'];
        if (is_string($data['data'])) {
            $data['data'] = json_decode($data['data']);
        }
        /**
         * Определяем тип модуля
         */
        Components::initModules();
        if(isset($data['data']->type->id)){
            $data['data']->type =   $data['data']->type->id;
        }
        if (isset(Components::$modules[$data['data']->type])) {

            /**
             * @var $moduleInstance ModuleInterface
             */
//            $moduleInstance = new Components::$modules[$data['data']->type]();
//
//            if (method_exists($moduleInstance, 'getModuleType')) {
//                $allowModuleType = $moduleInstance->getModuleType();
//                $currentType = array_last(explode('::', $data['data']->type));
//                if (isset($allowModuleType['items'])) {
//                    $detectedType = array_first(array_filter($allowModuleType['items'], function ($item) use ($currentType) {
//                        if ($item['id'] == $currentType) {
//
//                            return $item;
//                        }
//                    }));
//                    if ($detectedType) {
//                        $data['type'] = $detectedType;
//                    }
//                } else {
//                    dd($allowModuleType);
//                }
//            } else {
//                dd($moduleInstance);
//            }
        }
        unset($data['data']);
        $data['extra'] = trans('app.Тип модуля') . ': ' . $data['type']['name'];
        $event->setData($data);
    }

}