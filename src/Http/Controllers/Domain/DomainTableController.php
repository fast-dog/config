<?php

namespace FastDog\Config\Http\Controllers\Domain;


use FastDog\Config\Events\DomainsItemsAdminPrepare;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\Domain;
use FastDog\Core\Table\Interfaces\TableControllerInterface;
use FastDog\Core\Table\Traits\TableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Домены - Таблица
 *
 * @package FastDog\Config\Http\Controllers\Domain
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainTableController extends Controller implements TableControllerInterface
{
    use  TableTrait;

    /**
     * Модель по которой будет осуществляться выборка данных
     *
     * @var \FastDog\Core\Models\Domain|null $model
     */
    protected $model = null;

    /**
     * ContentController constructor.
     * @param Domain $model
     */
    public function __construct(Domain $model)
    {
        parent::__construct();
        $this->model = $model;
        $this->initTable();
        $this->page_title = trans('config::interface.Домены');
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
        $this->breadcrumbs->push(['url' => false, 'name' => trans('config::interface.Домены')]);

        event(new DomainsItemsAdminPrepare($result, $result['items']));

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
     * @param Request $request
     * @return JsonResponse
     */
    public function items(Request $request): JsonResponse
    {
        // TODO: Implement items() method.
    }
}