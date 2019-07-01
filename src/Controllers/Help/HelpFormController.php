<?php

namespace FastDog\Config\Controllers\Help;

use FastDog\Config\Entity\Help;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * Помощь администраторам - форма редактирования
 *
 * @package FastDog\Config\Controllers\Help
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class HelpFormController extends Controller implements FormControllerInterface
{
    use FormControllerTrait;

    /**
     * HelpFormController constructor.
     * @param Help $model
     */
    public function __construct(Help $model)
    {
        $this->model = $model;
        $this->page_title = trans('app.Помощь администраторам');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push(['url' => '/config/help', 'name' => trans('app.Управление')]);

        $result = $this->getItemData($request);
        if ($this->item) {
            $this->breadcrumbs->push(['url' => false, 'name' => $this->item->{Help::NAME}]);
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление страницы помощи администраторам
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postHelpSave(Request $request): JsonResponse
    {
        $result = [
            'success' => true,
            'items' => [],
        ];
        $item = null;
        $data = [
            Help::NAME => $request->input(Help::NAME),
            Help::ALIAS => $request->input(Help::ALIAS),
            Help::STATE => $request->input(Help::STATE . '.id', Help::STATE_PUBLISHED),
            Help::TEXT => $request->input(Help::TEXT),
            Help::DATA => json_encode($request->input(Help::DATA)),
        ];
        if ($request->input('id') > 0) {
            $item = Help::find($request->input('id'));
            if ($item) {
                Help::where('id', $item->id)->update($data);
                $item = Help::find($item->id);
            }
        } else {
            $item = Help::create($data);
        }

        return $this->json($result, __METHOD__);
    }

}