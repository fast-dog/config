<?php

namespace FastDog\Config\Http\Controllers\Components;


use FastDog\Config\Events\Components\ComponentItemsAdminPrepare as ComponentsItemsAdminPrepare;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Models\Components;
use FastDog\Core\Table\Interfaces\TableControllerInterface;
use FastDog\Core\Table\Traits\TableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Компоненты публичных страниц - Таблица
 *
 * @package FastDog\Config\Http\Controllers\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentsTableController extends Controller implements TableControllerInterface
{
    use  TableTrait;

    /**
     * Модель по которой будет осуществляться выборка данных
     *
     * @var \FastDog\Core\Models\Components|null $model
     */
    protected $model = null;

    /**
     * ComponentsTableController constructor.
     * @param Components $model
     */
    public function __construct(Components $model)
    {
        parent::__construct();
        $this->model = $model;
        $this->initTable();
        $this->page_title = trans('config::interface.Компоненты');
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
        $this->breadcrumbs->push(['url' => false, 'name' => trans('config::interface.Компоненты')]);

        foreach ($result['items'] as &$item) {
            event(new  ComponentsItemsAdminPrepare($item));
        }

        return $this->json($result, __METHOD__);
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
     * Описание структуры колонок таблицы
     *
     * @return Collection
     */
    public function getCols(): Collection
    {
        return $this->table->getCols();
    }

    /**
     * Поля для выборки по умолчанию
     *
     * @return array
     */
    public function getDefaultSelectFields(): array
    {
        return [Components::STATE, Components::DELETED_AT, Components::SITE_ID, Components::DATA];
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