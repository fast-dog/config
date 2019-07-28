<?php

namespace FastDog\Config\Http\Controllers\Localization;


use FastDog\Config\Models\Translate;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Локализация - форма
 *
 * @package FastDog\Config\Http\Controllers\Localization
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
        $this->page_title = trans('config::interface.Локализация');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push([
            'url' => '/configuration/localization',
            'name' => trans('config::interface.Локализация')
        ]);

        $result = $this->getItemData($request);

        $this->breadcrumbs->push([
            'url' => false,
            'name' => ($this->item) ? $this->item->{Translate::KEY} : trans('config::forms.localization.new')
        ]);


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
            Translate::SITE_ID => $request->input(Translate::SITE_ID . '.id'),
            Translate::STATE => $request->input(Translate::STATE . '.id'),
        ];
        if ($request->input('id') > 0) {
            $item = Translate::find($request->input('id'));
            if ($item) {
                Translate::where('id', $item->id)->update($data);
                $item = Translate::find($item->id);
            }
        } else {
            $item = Translate::create([
                Translate::CODE => $request->input(Translate::CODE),
                Translate::KEY => $request->input(Translate::KEY),
                Translate::VALUE => $request->input(Translate::VALUE),
                Translate::SITE_ID => $request->input(Translate::SITE_ID . '.id'),
                Translate::STATE => $request->input(Translate::STATE . '.id'),
            ]);
            $request->merge([
                'id' => $item->id
            ]);
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
    public function postLocalizationUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];

        try {
            $this->updatedModel($request->all(), Translate::class);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], __METHOD__);
        }
        return $this->json($result, __METHOD__);
    }

}
