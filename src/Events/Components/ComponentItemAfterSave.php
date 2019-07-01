<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 30.05.2018
 * Time: 10:50
 */

namespace FastDog\Config\Events\Components;

use App\Core\Interfaces\AdminPrepareEventInterface;
use App\Core\Module\Components;

/**
 * После сохранения модели
 *
 * @package FastDog\Config\Events\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemAfterSave implements AdminPrepareEventInterface
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
     * @var array
     */
    protected $result;

    /**
     * ComponentItemAfterSave constructor.
     * @param array $data
     * @param Components $item
     * @param array $result
     */
    public function __construct(array &$data, Components &$item, $result)
    {
        $this->data = &$data;
        $this->item = &$item;
        $this->result = &$result;
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

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

}