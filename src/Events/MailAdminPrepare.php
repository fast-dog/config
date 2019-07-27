<?php

namespace FastDog\Config\Events;


use FastDog\Config\Models\Emails;
use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Редактирование почтового шаблона
 *
 * @package FastDog\Config\Events
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class MailAdminPrepare implements AdminPrepareEventInterface
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
     * @var Emails $item
     */
    protected $item;

    /**
     * DomainsItemAdminPrepare constructor.
     * @param array $data
     * @param Emails|Messages $item
     * @param array $result
     */
    public function __construct(array &$data, &$item, array &$result)
    {
        $this->data = &$data;
        $this->item = &$item;
        $this->result = &$result;
    }

    /**
     * @return Emails
     */
    public function getItem(): Model
    {
        return $this->item;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getResult():array
    {
        return $this->result;
    }

    /**
     * @param $result
     * @return void
     */
    public function setResult(array $result):void
    {
        $this->result = $result;
    }
}
