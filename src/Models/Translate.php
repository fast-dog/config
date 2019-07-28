<?php

namespace FastDog\Config\Models;

use FastDog\Config\Events\Localization\LocalizationAdminPrepare;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Store;
use FastDog\Core\Table\Filters\BaseFilter;
use FastDog\Core\Table\Filters\Operator\BaseOperator;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * Обертка над локализацией
 *
 * @package FastDog\Config\Models
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Translate extends BaseModel implements TableModelInterface
{
    /**
     * Название сегмента локализации app,public etc.
     * @const string
     */
    const CODE = 'code';

    /**
     * Ключ термина перевода
     * @const string
     */
    const KEY = 'key';

    /**
     * Значение термина перевода
     * @const string
     */
    const VALUE = 'value';

    /**
     * @const string
     */
    const SITE_ID = 'site_id';

    /**
     * Название таблицы
     * @var string $table
     */
    public $table = 'config_translate';

    /**
     * Массив заполнения
     * @var array $fillable
     */
    public $fillable = [self::CODE, self::KEY, self::VALUE, self::SITE_ID];

    /**
     * Метки времени
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array
     */
    public function getData(): array
    {
        $result = [
            'id' => $this->id,
            self::KEY => $this->{self::KEY},
            self::CODE => $this->{self::CODE},
            self::VALUE => $this->{self::VALUE},
            self::STATE => $this->{self::STATE},
            self::SITE_ID => $this->{self::SITE_ID},
        ];

        return $result;
    }

    /**
     * Сегмент перевода
     *
     * Возвращает запрошенный сегмент перевода для пуличной части сайта, если в б.д. отсутсвуют значения,
     * то создает их
     *
     * @param $code string
     * @param $translate
     * @param bool $update
     * @return array|null
     */
    public static function getSegment($code, $translate, $update = false)
    {
        $key = __METHOD__ . '::' . DomainManager::getSiteId() . '::core-translate-' . $code;
        $isRedis = config('cache.default') == 'redis';
        $result = ($isRedis) ? \Cache::tags(['core'])->get($key, null) : \Cache::get($key, null);
        if ($update || \Request::input('reload_language_segment', 'N') === 'Y') {
            $result = null;
        }
        if ($result === null) {
            /**
             * @var $items Collection
             */
            $items = self::where(self::CODE, $code)->get();
            if ($items->isEmpty()) {
                foreach ($translate as $key => $value) {
                    if ($key !== '') {
                        self::create([
                            self::CODE => $code,
                            self::KEY => $key,
                            self::VALUE => $value,
                            self::SITE_ID => DomainManager::getSiteId(),
                        ]);
                    }
                }
                $result = $translate;
            } else {
                foreach ($items as $item) {
                    $result[$item->{self::KEY}] = $item->{self::VALUE};
                    unset($translate[$item->{self::KEY}]);
                }
                if (count($translate)) {
                    foreach ($translate as $key => $value) {
                        if ($key !== '') {
                            $result[$key] = $value;
                            self::firstOrCreate([
                                self::CODE => $code,
                                self::KEY => $key,
                                self::VALUE => $value,
                                self::SITE_ID => DomainManager::getSiteId(),
                            ]);
                        }
                    }
                }
                if ($isRedis) {
                    \Cache::tags(['core'])->put($key, $result, config('cache.tll_translate', 1));
                } else {
                    \Cache::put($key, $result, config('cache.tll_translate', 1));
                }
            }
        }

        return $result;
    }

    /**
     * Загрузка всех терминов перевода
     */
    public static function loadAllSegment()
    {
        /**
         * @var $storeManager Store
         */
        $storeManager = \App::make(Store::class);

        $result = [];
        /**
         * @var $items Collection
         */
        $items = self::where(function(Builder $query) {
            $query->where(self::SITE_ID, DomainManager::getSiteId());
        })->get();

        $items->each(function($item, $idx) use (&$result) {
            $result[$item->code][] = [
                'id' => $item->id,
                'key' => $item->key,
                'value' => $item->value,
            ];
        });

        foreach ($result as $code => $results) {
            $storeManager->pushCollection($code, $results);
        }
    }

    /**
     * Вывод терминов локализации из базы данных
     *
     * @param $code
     * @return array
     */
    public static function getSegmentAdmin($code)
    {
        /**
         * @var $storeManager Store
         */
        $storeManager = \App::make(Store::class);
        $result = $storeManager->getCollection($code);

        if (null === $result && 1 == 0) {
            try {
                $result = [];
                /**
                 * @var $items Collection
                 */
                $items = self::where(self::CODE, $code)->get();

                $items->each(function($item, $idx) use (&$result) {
                    array_push($result, [
                        'id' => $item->id,
                        'key' => $item->key,
                        'value' => $item->value,
                    ]);
                });
                $storeManager->pushCollection($code, $result);
            } catch (\Exception $e) {
                dd($e);
            }
        }

        return $result;
    }

    /**
     * Удаляет сегмент из б.д.
     *
     * @param $code
     * @return mixed
     */
    public static function deleteSegment($code)
    {
        return self::where(self::CODE, $code)->delete();
    }

    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     * @return string
     */
    public function getEventAdminPrepareName(): string
    {
        return LocalizationAdminPrepare::class;
    }

    /**
     * Возвращает описание доступных полей для вывода в колонки...
     *
     * ... метод используется для первоначального конфигурирования таблицы,
     * дальнейшие типы, порядок колонок и т.д. будут храниться в обхекте BaseTable
     *
     * @return array
     */
    public function getTableCols(): array
    {
        return [
            [
                'name' => trans('config::interface.Название'),
                'key' => self::KEY,
                'domain' => true,
                'callback' => false,
                'link' => 'localization_item',
                'extra' => true,
                'action' => [
                    'edit' => true,
                    'replicate' => true,
                    'delete' => true,
                ]
            ],
            [
                'name' => trans('config::interface.Значение'),
                'key' => self::VALUE,
                'domain' => false,
                'callback' => false,
                'link' => 'localization_item',
                'extra' => true,
            ],
            [
                'name' => '#',
                'key' => 'id',
                'link' => null,
                'width' => 80,
                'class' => 'text-center',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAdminFilters(): array
    {
        $siteIds = DomainManager::getAccessDomainList();
        $siteId = DomainManager::getSiteId();
        $default = [
            [
                [
                    BaseFilter::NAME => self::VALUE,
                    BaseFilter::PLACEHOLDER => trans('config::forms.localization.general.code'),
                    BaseFilter::TYPE => BaseFilter::TYPE_TEXT,
                    BaseFilter::DISPLAY => true,
                    BaseFilter::OPERATOR => (new BaseOperator('LIKE', 'LIKE'))->getOperator(),
                ],
            ],
            [
                BaseFilter::getLogicAnd(),
                [
                    'id' => self::SITE_ID,
                    BaseFilter::NAME => self::SITE_ID,
                    BaseFilter::PLACEHOLDER => trans('config::forms.localization.general.access'),
                    BaseFilter::TYPE => BaseFilter::TYPE_SELECT,
                    BaseFilter::DATA => $siteIds,
                    BaseFilter::OPERATOR => (new BaseOperator())->getOperator(),
                    BaseFilter::DISPLAY => true,
                    'value' => Arr::first(array_filter($siteIds, function($value) use ($siteId) {
                        return $value['id'] == $siteId;
                    })),
                ],
            ],
        ];

        return $default;
    }

}
