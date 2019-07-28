<?php

namespace FastDog\Config\Http\Controllers\Help;

use FastDog\Config\Models\ConfigHelp;
use FastDog\Core\Form\Interfaces\FormControllerInterface;
use FastDog\Core\Form\Traits\FormControllerTrait;
use FastDog\Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * Помощь администраторам - форма редактирования
 *
 * @package FastDog\Config\Http\Controllers\Help
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class HelpFormController extends Controller implements FormControllerInterface
{
    use FormControllerTrait;

    /**
     * HelpFormController constructor.
     * @param ConfigHelp $model
     */
    public function __construct(ConfigHelp $model)
    {
        $this->model = $model;
        $this->page_title = trans('config::interface.Помощь администраторам');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditItem(Request $request): JsonResponse
    {
        $this->breadcrumbs->push([
            'url' => '/configuration/help',
            'name' => trans('config::interface.Помощь администраторам')
        ]);

        $result = $this->getItemData($request);

        $this->breadcrumbs->push([
            'url' => false,
            'name' => ($this->item) ? $this->item->{ConfigHelp::NAME} : trans('config::forms.help.new')
        ]);


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
