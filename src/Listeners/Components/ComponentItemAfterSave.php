<?php
namespace FastDog\Config\Listeners\Components;


use App\Core\Module\Components;
use FastDog\Config\Events\Components\ComponentItemAfterSave as EventComponentItemAfterSave;
use Illuminate\Http\Request;

/**
 * После сохранения
 *
 * @package FastDog\Config\Listeners\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemAfterSave
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
     * @param  EventComponentItemAfterSave $event
     * @return void
     */
    public function handle(EventComponentItemAfterSave $event)
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