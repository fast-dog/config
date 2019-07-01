<?php
namespace FastDog\Config\Controllers\Localization;



use FastDog\Config\Entity\Translate;
use FastDog\Core\Http\Controllers\Controller;
use FastDog\Core\Table\Interfaces\TableControllerInterface;
use FastDog\Core\Table\Traits\TableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Локализация - таблица
 *
 * @package FastDog\Config\Controllers\Localization
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class LocalizationTableController extends Controller implements TableControllerInterface
{
    use  TableTrait;


    /**
     * Модель по которой будет осуществляться выборка данных
     *
     * @var \FastDog\Config\Entity\Translate|null $model
     */
    protected $model = null;

    /**
     * ContentController constructor.
     * @param Translate $model
     */
    public function __construct(Translate $model)
    {
        parent::__construct();
        $this->model = $model;
        $this->initTable();
        $this->page_title = trans('app.Локализация');
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
     * Поля для выборки по умолчанию
     *
     * @return array
     */
    public function getDefaultSelectFields(): array
    {
        return [Translate::STATE, Translate::DELETED_AT, Translate::SITE_ID];
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