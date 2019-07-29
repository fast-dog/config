<?php

namespace FastDog\Config\Models;

use FastDog\Config\Events\HelpAdminPrepare;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Table\Filters\BaseFilter;
use FastDog\Core\Table\Filters\Operator\BaseOperator;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use Illuminate\Support\Collection;

/**
 *
 * @package FastDog\Config\Models
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ConfigHelp extends BaseModel implements TableModelInterface
{
    /**
     * Описание
     * @const string
     */
    const TEXT = 'text';


    /**
     * Дополнительные параметры
     * @const string
     */
    const DATA = 'data';

    /**
     * Название таблицы
     * @var string $table
     */
    public $table = 'config_help';

    /**
     * Автозаполнение
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::ALIAS, self::TEXT, self::DATA];

    /**
     * @return array
     */
    public function getData(): array
    {
        $item = $this;
        $data = [
            'id' => $item->id,
            self::NAME => $item->{self::NAME},
            self::ALIAS => $item->{self::ALIAS},
            self::STATE => $item->{self::STATE},
            self::TEXT => $item->{self::TEXT},
            self::DATA => json_decode($item->{self::DATA}),
        ];

        return $data;
    }


    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     * @return string
     */
    public function getEventAdminPrepareName(): string
    {
        return HelpAdminPrepare::class;
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
                'name' => trans('config::forms.help.general.fields.name'),
                'key' => self::NAME,
                'domain' => true,
                'callback' => false,
                'link' => 'help_item',
                'action' => [
                    'edit' => true,
                    'replicate' => true,
                    'delete' => true,
                ]
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
        $default = [
            [
                [
                    BaseFilter::NAME => self::NAME,
                    BaseFilter::PLACEHOLDER => trans('config::forms.help.general.fields.name'),
                    BaseFilter::TYPE => BaseFilter::TYPE_TEXT,
                    BaseFilter::DISPLAY => true,
                    BaseFilter::OPERATOR => (new BaseOperator('LIKE', 'LIKE'))->getOperator(),
                ],
            ],
        ];

        return $default;
    }

    /**
     * @return Collection
     */
    public function getDefaultProperties(): Collection
    {
        $result = [];

        return collect($result);
    }
}
