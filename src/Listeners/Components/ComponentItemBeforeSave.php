<?php
namespace FastDog\Config\Listeners\Components;



use FastDog\Config\Events\Components\ComponentItemBeforeSave as EventComponentItemBeforeSave;
use FastDog\Core\Models\Components;
use Illuminate\Http\Request;

/**
 * После сохранения
 *
 * @package FastDog\Config\Listeners\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemBeforeSave
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
     * @param  EventComponentItemBeforeSave $event
     * @return void
     */
    public function handle(EventComponentItemBeforeSave $event)
    {
        /**
         * @var $item Components
         */
        $item = $event->getItem();

        /**
         * @var $data array
         */
        $data = $event->getData();


        $event->setData($data);
    }
}