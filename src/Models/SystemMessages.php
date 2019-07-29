<?php

namespace FastDog\Config\Models;

use FastDog\Core\Models\BaseModel;
use stdClass;

/**
 * Шаблоны личных сообщений
 *
 * @package FastDog\Config\Models
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class SystemMessages extends BaseModel
{
    /**
     * Текст шаблона
     * @const string
     */
    const  TEXT = 'text';

    /**
     * Имя таблицы в базе данных
     * @var string $table 'system_messages'
     */
    public $table = 'system_messages';

    /**
     * Массив полей автозаполнения
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::ALIAS, self::TEXT, self::DATA, self::STATE, self::SITE_ID];

    /**
     * Детальная информация по объекту
     * @return array
     */
    public function getData(): array
    {
        if (is_string($this->{self::DATA})) {
            $this->{self::DATA} = json_decode($this->{self::DATA});
        }

        $data = [
            'id' => (int)$this->id,
            self::NAME => $this->{self::NAME},
            self::ALIAS => $this->{self::ALIAS},
            self::STATE => $this->{self::STATE},
            self::TEXT => $this->{self::TEXT},
            self::DATA => $this->{self::DATA},
        ];

        return $data;
    }


    /**
     * Отправка системных сообщений
     *
     * @param null|StdClass $name имя шаблона или объект с полем text для отправки
     * @param array $params
     * @return bool
     */
    public static function send($name = null, $params = ['title' => 'title message'])
    {
        if (null == $name) {
            return false;
        }
        $text = null;
        /**
         * @var $tpl self
         */
        $tpl = self::where(function ($query) use ($name) {
            $query->where(self::ALIAS, $name);
            $query->where(self::STATE, self::STATE_PUBLISHED);
        })->first();
        if (is_string($name)) {
            if (isset($tpl->text)) {
                $text = $tpl->text;
            }
        } else if ($name instanceof StdClass) {
            $text = $name->text;

        }
        $text = str_replace('&gt;', '>', $text);

        if ($text) {
            foreach ($params as $key => $value) {
                $key = strtoupper($key);
                if (!is_object($value)) {
                    $text = str_replace('{{' . $key . '}}', $value, $text);
                } else {
                    if (method_exists($value, 'getArrayableAttributes')) {
                        $attribs = $value->getArrayableAttributes();
                        foreach ($attribs as $_key => $_value) {
                            $text = str_replace('{{' . $key . '->' . $_key . '}}', $_value, $text);
                        }
                    }
                }
            }

            $params['content'] = $text;
            $params['title'] = $tpl->getParameterByFilterData(['name' => 'TITLE'], '');
            $params['title_header'] = $tpl->getParameterByFilterData(['name' => 'TITLE_HEADER'], '');
        }
    }
}
