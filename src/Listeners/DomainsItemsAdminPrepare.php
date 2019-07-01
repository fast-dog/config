<?php

namespace FastDog\Config\Listeners;


use FastDog\Config\Events\DomainsItemsAdminPrepare as DomainsItemsAdminPrepareEvent;
use FastDog\Core\Models\DomainManager;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Список доменов
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainsItemsAdminPrepare
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
     * @param DomainsItemsAdminPrepareEvent $event
     */
    public function handle(DomainsItemsAdminPrepareEvent $event)
    {
        $data = $event->getData();

        if (isset($data['items'])) {
            if (DomainManager::checkIsDefault()) {
                foreach ($data['items'] as &$item) {
                    $item['suffix'] = DomainManager::getDomainSuffix($item[DomainManager::CODE]);
                }
            }
        }
        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }

}