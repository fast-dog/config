<?php

namespace FastDog\Config\Controllers\Emails;


use FastDog\Config\Entity\Emails;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\BaseModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Почтоыфе события - форма редактирования
 *
 * @package FastDog\Config\Controllers\Domain
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class EmailsFormController extends Controller implements FormControllerInterface
{
    use FormControllerTrait;

    /**
     * DomainFormController constructor.
     * @param Emails $model
     */
    public function __construct(Emails $model)
    {
        $this->model = $model;
        $this->page_title = trans('app.Почтовые события');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push(['url' => '/config/emails', 'name' => trans('app.Управление')]);

        $result = $this->getItemData($request);
        if ($this->item) {
            $this->breadcrumbs->push(['url' => false, 'name' => $this->item->{BaseModel::NAME}]);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Добавление\обновление домена
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postUpdate(Request $request): JsonResponse
    {
        $result = ['success' => true];
        /**
         * @var $item Emails|null
         */
        $item = null;

        $data = [
            Emails::NAME => $request->input(Emails::NAME),
            Emails::ALIAS => $request->input(Emails::ALIAS),
            Emails::TEXT => $request->input(Emails::TEXT),
            Emails::SITE_ID => $request->input(Emails::SITE_ID . '.id'),
            Emails::DATA => json_encode($request->input(Emails::DATA)),
        ];
        if ($request->input('id') > 0) {
            $item = Emails::find($request->input('id'));
            if ($item) {
                Emails::where('id', $item->id)->update($data);
                $item = Emails::find($item->id);
            }
        } else {
            $item = Emails::create($data);
            $request->merge([
                'id' => $item->id,
            ]);
        }

        if ($request->has('properties')) {
            $item->storeProperties(collect($request->input('properties', [])));
        }

        if ($item) {
            $result = $this->getItemData($request);
        }


        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление основных параметров модели из таблицы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEmailsUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), Emails::class);

        return $this->json($result, __METHOD__);
    }
}