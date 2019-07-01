<?php

namespace FastDog\Config\Controllers\Localization;

use FastDog\Config\Entity\Translate;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Локализация - форма
 *
 * @package FastDog\Config\Controllers\Localization
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class LocalizationFormController extends Controller implements FormControllerInterface
{
    use FormControllerTrait;

    /**
     * LocalizationFormController constructor.
     * @param Translate $model
     */
    public function __construct(Translate $model)
    {
        $this->model = $model;
        $this->page_title = trans('app.Локализация');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push(['url' => '/config/localization', 'name' => trans('app.Управление')]);

        $result = $this->getItemData($request);
        if ($this->item) {
            $this->breadcrumbs->push(['url' => false, 'name' => $this->item->{Translate::KEY}]);
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
         * @var $item Translate|null
         */
        $item = null;

        $data = [
            Translate::VALUE => $request->input(Translate::VALUE),

        ];
        if ($request->input('id') > 0) {
            $item = Translate::find($request->input('id'));
            if ($item) {
                Translate::where('id', $item->id)->update($data);
                $item = Translate::find($item->id);
            }
        }

        if ($item) {
            $result = $this->getItemData($request);
        }


        return $this->json($result, __METHOD__);
    }

}