<?php

namespace FastDog\Config\Listeners;

use FastDog\Config\Models\Emails;
use Illuminate\Http\Request;
use FastDog\Config\Events\MailAdminPrepare as MailAdminPrepareEvent;
use stdClass;

/**
 * Редактирование почтового шаблона
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class MailAdminPrepare
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
     * @param MailAdminPrepareEvent $event
     */
    public function handle(MailAdminPrepareEvent $event)
    {
        /**
         * @var $item Emails
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if ($data['id'] == 0) {
            $data['data'] = new StdClass();
        }

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }

        $data['properties'] = $item->properties();

        $event->setData($data);
        $event->setResult($result);
    }

}