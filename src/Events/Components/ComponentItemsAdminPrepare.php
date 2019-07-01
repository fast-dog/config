<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.12.2016
 * Time: 15:36
 */

namespace FastDog\Config\Events\Components;


use FastDog\Config\Entity\DomainManager;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Список публичных моудлей
 *
 * @package FastDog\Config\Events\Components
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ComponentItemsAdminPrepare
{
    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * PublicModulesItemsAdminPrepare constructor.
     * @param array $data
     */
    public function __construct(array &$data)
    {
        $this->data = &$data;
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
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}