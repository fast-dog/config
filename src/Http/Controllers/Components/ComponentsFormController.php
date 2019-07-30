<?php

namespace FastDog\Config\Http\Controllers\Components;


use FastDog\Config\Events\Components\ComponentItemAfterSave;
use FastDog\Config\Events\Components\ComponentItemBeforeSave;
use FastDog\Config\Http\Request\AddSiteModule;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\Components;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Компоненты публичных страниц - Форма
 *
 * @package FastDog\Config\Http\Controllers\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentsFormController extends Controller implements FormControllerInterface
{
    use FormControllerTrait;

    /**
     * ComponentsFormController constructor.
     * @param Components $model
     */
    public function __construct(Components $model)
    {
        $this->model = $model;
        $this->page_title = trans('config::interface.Компоненты');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push([
            'url' => '/configuration/components',
            'name' => trans('config::interface.Компоненты')]);

        $result = $this->getItemData($request);

        $this->breadcrumbs->push([
            'url' => false,
            'name' => ($this->item->id) ? $this->item->{Components::NAME} : trans('config::forms.components.new'),
        ]);


        return $this->json($result, __METHOD__);
    }


    /**
     * Обновление публичного модуля
     *
     * @param AddSiteModule $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdate(AddSiteModule $request)
    {
        $result = ['success' => true,];
        $data = [
            Components::NAME => $request->input(Components::NAME),
            Components::STATE => $request->input(Components::STATE . '.id', Components::STATE_PUBLISHED),
            Components::SITE_ID => $request->input(Components::SITE_ID . '.id'),
            Components::DATA => json_encode($request->input(Components::DATA)),
        ];
        if ($data[Components::STATE] === null) {
            $data[Components::STATE] = Components::STATE_PUBLISHED;
        }

        if ($request->input('id') !== null) {
            $item = Components::find($request->input('id'));
            event(new ComponentItemBeforeSave($data, $item));
            unset($data['_events']);
            Components::where('id', $item->id)->update($data);
        } else {
            $model = new Components();
            event(new ComponentItemBeforeSave($data, $model));
            unset($data['_events']);
            $item = Components::create($data);
            $request->merge([
                'id' => $item->id,
            ]);
        }

        event(new ComponentItemAfterSave($data, $item, $result));


        $result = $this->getItemData($request);

        return $this->json($result, __METHOD__);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postReplicate(Request $request)
    {
        $result = ['success' => true];
        if ($request->input('id') !== null) {
            $item = Components::find($request->input('id'));

        }

        return $this->json($result, __METHOD__);
    }

}

