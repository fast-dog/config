<?php

namespace FastDog\Config\Controllers\Help;


use FastDog\Config\Entity\Help;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Table\Interfaces\TableControllerInterface;
use FastDog\Core\Table\Traits\TableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Помощь администраторам - таблица
 *
 * @package FastDog\Config\Controllers\Help
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class HelpTableController extends Controller implements TableControllerInterface
{
    use  TableTrait;


    /**
     * Модель по которой будет осуществляться выборка данных
     *
     * @var \FastDog\Config\Entity\Help|null $model
     */
    protected $model = null;

    /**
     * ContentController constructor.
     * @param Help $model
     */
    public function __construct(Help $model)
    {
        parent::__construct();
        $this->model = $model;
        $this->initTable();
        $this->page_title = trans('app.Помощь администраторам');
    }

    /**
     * Модель, контекст выборок
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Таблица - Домены
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $result = self::paginate($request);
        $this->breadcrumbs->push(['url' => false, 'name' => trans('app.Управление')]);

        return $this->json($result, __METHOD__);
    }

    /**
     * Описание структуры колонок таблицы
     *
     * @return Collection
     */
    public function getCols(): Collection
    {
        return $this->table->getCols();
    }

    /**
     * Обновление сообщений из списка
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postHelpSelfUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), Help::class);

        return $this->json($result, __METHOD__);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function items(Request $request): JsonResponse
    {
        // TODO: Implement items() method.
    }
}