<?php

namespace FastDog\Config\Listeners;


use FastDog\Config\Events\HelpAdminPrepare as HelpAdminPrepareEvent;
use FastDog\Config\Models\Emails;
use Illuminate\Http\Request;
use stdClass;

/**
 * Редактирование почтового шаблона
 *
 * @package FastDog\Config\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class HelpAdminPrepare
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
     * @param HelpAdminPrepareEvent $event
     */
    public function handle(HelpAdminPrepareEvent $event)
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
        $data['created_at'] = ($item->created_at !== null) ? $item->created_at->format('Y-m-d') : '';
        $data['updated_at'] = ($item->updated_at !== null) ? $item->updated_at->format('Y-m-d') : '';
        $data['published_at'] = ($item->published_at !== null) ? $item->published_at->format('Y-m-d') : '';

        $data['hide_category'] = 'Y';
        $data['hide_description'] = 'Y';
        $data['hide_media'] = 'Y';
        $data['hide_seo'] = 'Y';
        $data['hide_sample_property'] = 'N';
        $data['hide_html'] = 'N';
        $data['alias_change'] = ($data['id']) ? 'N' : 'Y';


        $data['properties'] = [];

        //Параметры отображения формы редактирования элементов по умолчанию
        $base = [
            ['name' => 'TITLE', 'value' => '', 'sort' => 400, 'type' => 'system'],
            ['name' => 'TITLE_HEADER', 'value' => '', 'sort' => 500, 'type' => 'system'],
        ];
        if (!$data['data']) {
            $data['data'] = new StdClass();
        }

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