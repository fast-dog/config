<?php
namespace FastDog\Config\Listeners;


use FastDog\Config\Config;
use FastDog\Core\Models\Domain;
use FastDog\Core\Models\DomainManager;
use Illuminate\Http\Request;
use FastDog\Config\Events\DomainsItemAdminPrepare as DomainsItemAdminPrepareEvent;


/**
 * Редактирование домена
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainsItemAdminPrepare
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
     * @param DomainsItemAdminPrepareEvent $event
     */
    public function handle(DomainsItemAdminPrepareEvent $event)
    {
        /**
         * @var $item Domain
         */
        $item = $event->getItem();
        $data = $event->getData();
        $result = $event->getResult();

        if (config('app.debug')) {
            $result['_events'][] = __METHOD__;
        }

        /**
         * Получение списка ACL
         */
        $data['acl'] = [];// Config::getAcl($item->{Domain::CODE});

        $data['properties'] = $item->properties();

        $data[Domain::LANG] = array_first(array_filter(DomainManager::getAllowLang(), function ($element) use ($data) {

            return $element['id'] == $data[Domain::LANG];
        }));


        $event->setData($data);
        $event->setResult($result);
    }

}