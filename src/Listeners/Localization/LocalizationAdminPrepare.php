<?php
namespace FastDog\Config\Listeners\Localization;

use FastDog\Config\Models\Translate;
use Illuminate\Http\Request;
use FastDog\Config\Events\Localization\LocalizationAdminPrepare as LocalizationAdminPrepareEvent;

/**
 * Class LocalizationAdminPrepare
 * @package FastDog\Config\Listeners\Localization
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class LocalizationAdminPrepare
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
     * @param LocalizationAdminPrepareEvent $event
     */
    public function handle(LocalizationAdminPrepareEvent $event)
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

        $event->setData($data);
        $event->setResult($result);
    }
}
