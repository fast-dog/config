<?php

namespace FastDog\Config\Controllers\Domain;


use FastDog\Config\Request\AddDomain;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\Domain;
use FastDog\Core\Models\DomainManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Домены - форма редактирования
 *
 * @package FastDog\Config\Controllers\Domain
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainFormController extends Controller implements FormControllerInterface
{
    use FormControllerTrait;

    /**
     * DomainFormController constructor.
     * @param Domain $model
     */
    public function __construct(Domain $model)
    {
        $this->model = $model;
        $this->page_title = trans('app.Домены');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push(['url' => '/config/domains', 'name' => trans('app.Управление')]);

        $result = $this->getItemData($request);
        if ($this->item) {
            $this->breadcrumbs->push(['url' => false, 'name' => $this->item->{BaseModel::NAME}]);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Добавление\обновление домена
     *
     * @param AddDomain $request
     * @return JsonResponse
     */
    public function postUpdate(AddDomain $request): JsonResponse
    {
        $result = ['success' => true];

        $data = [
            'id' => $request->input('id'),
            Domain::NAME => $request->input(Domain::NAME),
            Domain::URL => $request->input(Domain::URL),
            Domain::CODE => $request->input(Domain::CODE),
            Domain::SITE_ID => $request->input(Domain::SITE_ID . '.id'),
            Domain::LANG => $request->input(Domain::LANG . '.id'),
            Domain::STATE => $request->input(Domain::STATE . '.id'),
        ];

        if ((int)$data['id'] > 0) {
            DomainManager::where('id', $data['id'])->update($data);
            $item = DomainManager::where('id', $data['id'])->first();
        } else {
            $item = DomainManager::create($data);
            $request->merge(['id' => $item->id]);

            $result = $this->getItemData($request);
        }
        $requestData = $request->all();
        /**
         * Сохранение дополнительных параметров
         */
        if ((isset($requestData['properties']) && count($requestData['properties']) > 0) && method_exists($item, 'storeProperties')) {
            $item->storeProperties(collect($requestData['properties']));
        }


        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление основных параметров модели из таблицы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDomainsUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), Domain::class);

        return $this->json($result, __METHOD__);
    }
}