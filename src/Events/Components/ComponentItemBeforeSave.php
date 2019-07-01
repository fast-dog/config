<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 30.05.2018
 * Time: 10:31
 */

namespace FastDog\Config\Events\Components;

use App\Core\Interfaces\AdminPrepareEventInterface;
use App\Core\Module\Components;

/**
 * До сохранения модели
 *
 * @package FastDog\Config\Events\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemBeforeSave implements AdminPrepareEventInterface
{
    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var Components $item
     */
    protected $item;

    /**
     * ComponentItemBeforeSave constructor.
     * @param array $data
     * @param Components $item
     */
    public function __construct(array &$data, Components &$item = null)
    {
        $this->data = &$data;

        $this->item = &$item;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return Components
     */
    public function getItem(): Components
    {
        return $this->item;
    }

    /**
     * @param Components $item
     */
    public function setItem(Components $item)
    {
        $this->item = $item;
    }


    public function getResult()
    {
        // TODO: Implement getResult() method.
    }

    public function setResult($result)
    {
        // TODO: Implement setResult() method.
    }
}