<?php

namespace FastDog\Config\Http\Controllers\Help;

use FastDog\Config\Models\ConfigHelp;
use FastDog\Config\Request\AddHelp;
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
            'name' => ($this->item->id > 0) ? $this->item->{ConfigHelp::NAME} : trans('config::forms.help.new')
        ]);


        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление страницы помощи администраторам
     *
     * @param AddHelp $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postHelpSave(AddHelp $request): JsonResponse
    {
        $result = [
            'success' => true,
            'items' => [],
        ];
        /** @var ConfigHelp $item */
        $item = null;
        $data = [
            ConfigHelp::NAME => $request->input(ConfigHelp::NAME),
            ConfigHelp::ALIAS => $request->input(ConfigHelp::ALIAS),
            ConfigHelp::STATE => $request->input(ConfigHelp::STATE . '.id', ConfigHelp::STATE_PUBLISHED),
            ConfigHelp::TEXT => $request->input(ConfigHelp::TEXT),
            ConfigHelp::DATA => json_encode($request->input(ConfigHelp::DATA)),
        ];
        if ($request->input('id') > 0) {
            $item = ConfigHelp::find($request->input('id'));
            if ($item) {
                ConfigHelp::where('id', $item->id)->update($data);
                $item = ConfigHelp::find($item->id);
            }
        } else {
            $item = ConfigHelp::create($data);
        }

        array_push($result['items'], $item->getData());

        return $this->json($result, __METHOD__);
    }

}
