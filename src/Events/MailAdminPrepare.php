<?php

namespace FastDog\Config\Events;

use App\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Config\Entity\Emails;
use FastDog\Config\Entity\Messages;


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
     * @return Emails|Messages
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
