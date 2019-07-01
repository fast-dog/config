<?php

namespace FastDog\Config\Listeners;



use FastDog\Config\Events\ServiceAdminPrepare as ServiceAdminPrepareEvent;
use FastDog\User\Models\Services\Service;
use Illuminate\Http\Request;
use stdClass;

/**
 * Редактирование почтового шаблона
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ServiceAdminPrepare
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
     * @param ServiceAdminPrepareEvent $event
     */
    public function handle(ServiceAdminPrepareEvent $event)
    {
        /**
         * @var $item Service
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
        $data['created_at'] = ($item->created_at !== null) ? $item->created_at->format('Y-m-d') : '';
        $data['updated_at'] = ($item->updated_at !== null) ? $item->updated_at->format('Y-m-d') : '';
        $data['published_at'] = ($item->published_at !== null) ? $item->published_at->format('Y-m-d') : '';

        $data['hide_category'] = 'Y';
        $data['hide_description'] = 'N';
        $data['hide_media'] = 'Y';
        $data['hide_seo'] = 'Y';
        $data['hide_sample_property'] = 'N';
        $data['hide_html'] = 'Y';
        $data['hide_alias'] =  'Y';


        $data['properties'] = [];

        //Параметры отображения формы редактирования элементов по умолчанию
        $base = [
            ['name' => 'MAX_VALUE', 'value' => 30, 'sort' => 100, 'type' => 'system'],
            ['name' => 'EXTEND_VALUE', 'value' => 20, 'sort' => 200, 'type' => 'system'],
        ];

        if (!isset($data['data']->properties)) {
            $tmp = [];
            foreach ($base as $_key => $_item) {
                array_push($tmp, $_item);
            }
            $data['data']->properties = (object)$tmp;
        }

        $event->setData($data);
        $event->setResult($result);
    }

}