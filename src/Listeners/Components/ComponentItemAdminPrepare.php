<?php
namespace FastDog\Config\Listeners\Components;


use App\Core\Module\Components;
use FastDog\Config\Events\Components\ComponentItemAdminPrepare as PublicModuleItemAdminPrepareEvent;
use FastDog\Media\Models\GalleryItem;
use Illuminate\Http\Request;

/**
 * Редактирование публичного модуля
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemAdminPrepare
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
     * @param PublicModuleItemAdminPrepareEvent $event
     * @return void
     */
    public function handle(PublicModuleItemAdminPrepareEvent $event)
    {
        /**
         * @var $item Components
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }
        $data['properties'] = $item->properties();
        $data['media'] = $item->getMedia();

        $data['el_finder'] = [
            GalleryItem::PARENT_TYPE => GalleryItem::TYPE_PUBLIC_MODULES,
            GalleryItem::PARENT_ID => (isset($item->id)) ? $item->id : 0,
        ];


        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
        $event->setResult($result);
    }

}