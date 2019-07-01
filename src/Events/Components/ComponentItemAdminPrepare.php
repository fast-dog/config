<?php

namespace FastDog\Config\Events\Components;

use App\Core\Interfaces\AdminPrepareEventInterface;
use App\Core\Module\Components;
use FastDog\Config\Entity\Domain;


/**
 * Редактирование публичного модуля
 *
 * @package FastDog\Config\Events\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemAdminPrepare implements AdminPrepareEventInterface
{

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var array $result
     */
    protected $result = [];

    /**
     * @var Domain $item
     */
    protected $item;

    /**
     * DomainsItemAdminPrepare constructor.
     * @param array $data
     * @param Components $item
     * @param array $result
     */
    public function __construct(array &$data, Components &$item, array &$result)
    {
        $this->data = &$data;
        $this->item = &$item;
        $this->result = &$result;
    }

    /**
     * @return Components
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $result
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
