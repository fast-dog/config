<?php

namespace FastDog\Config\Models;

use FastDog\Config\Events\MailAdminPrepare;
use FastDog\Core\Media\Interfaces\MediaInterface;
use FastDog\Core\Media\Traits\MediaTraits;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Properties\BaseProperties;
use FastDog\Core\Properties\Interfases\PropertiesInterface;
use FastDog\Core\Properties\Traits\PropertiesTrait;
use FastDog\Core\Table\Filters\BaseFilter;
use FastDog\Core\Table\Filters\Operator\BaseOperator;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use FastDog\User\Models\UserSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Шаблоны email
 *
 * @package FastDog\Config\Models
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Emails extends BaseModel implements TableModelInterface, PropertiesInterface, MediaInterface
{
    use PropertiesTrait, MediaTraits;
    
    /**
     * Текст шаблона
     * @const string
     */
    const  TEXT = 'text';
    
    /**
     * Имя таблицы в базе данных
     * @var string $table 'config_emails'
     */
    public $table = 'config_emails';
    
    /**
     * Массив полей автозаполнения
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::ALIAS, self::TEXT, self::DATA, self::STATE, self::SITE_ID];
    
    /**
     * Детальная информация по объекту
     *
     * @return array
     */
    public function getData(): array
    {
        if (is_string($this->{self::DATA})) {
            $this->{self::DATA} = json_decode($this->{self::DATA});
        }
        
        $data = [
            'id' => (int) $this->id,
            self::NAME => $this->{self::NAME},
            self::ALIAS => $this->{self::ALIAS},
            self::STATE => $this->{self::STATE},
            self::TEXT => $this->{self::TEXT},
            self::DATA => $this->{self::DATA},
            self::SITE_ID => $this->{self::SITE_ID},
        ];
        
        return $data;
    }
    
    
    /**
     * Отправка системных сообщений
     *
     * @param  null|StdClass|BaseModel  $name  имя шаблона или объект с полем text для отправки
     * @param  array  $params
     * @return bool
     */
    public static function send($name = null, $params = ['title' => 'title message'])
    {
        if (null == $name) {
            return false;
        }
        $text = null;
        
        /**
         * Проверка настроек пользователя
         */
        if (isset($params['user'])) {
            if (false === $params['user']->setting->can(UserSettings::SEND_EMAIL_NOTIFY)) {
                if (!in_array($name, ['new_password', 'user_banned', 'system:change_email'])) {
                    return false;
                }
            }
        }
        
        if (is_string($name)) {
            /**@var $tpl self */
            $tpl = self::where(function (Builder $query) use ($name, $params) {
                $query->where(self::ALIAS, $name);
                $query->where(self::STATE, self::STATE_PUBLISHED);
                if (isset($params[self::SITE_ID])) {
                    $query->where(self::SITE_ID, $params[self::SITE_ID]);
                }
            })->first();
            
            if (isset($tpl->text)) {
                $text = $tpl->text;
            }
            
        } else {
            if ($name instanceof StdClass) {
                $tpl = new self();
                $text = $name->text;
            } else {
                if ($name instanceof BaseModel && isset($name->text)) {
                    $tpl = $name;
                    $text = $name->text;
                }
            }
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
                            $text = str_replace('{{' . strtoupper($key) . '->' . strtoupper($_key) . '}}', $_value, $text);
                        }
                    }
                }
            }
            
            $params['content'] = $text;
            $params['title'] = $tpl->getParameterByFilterData(['name' => 'TITLE'], '');
            $params['title_header'] = $tpl->getParameterByFilterData(['name' => 'TITLE_HEADER'], '');
            
            $result = \Mail::send('vendor.fast_dog.000.core.email.system', $params,
                function ($message) use ($tpl, $params) {
                    if (!isset($params['to'])) {
                        $params['to'] = $tpl->getParameterByFilterData(['name' => 'TO_ADDRESS'], null);
                    }
                    $message->to($params['to']);
                    
                    $message->from($tpl->getParameterByFilterData(['name' => 'FROM_ADDRESS'], config('mail.from.address')),
                        $tpl->getParameterByFilterData(['name' => 'FROM_NAME'], config('mail.from.name')));
                    if (!isset($params['subject'])) {
                        $message->subject($tpl->getParameterByFilterData(['name' => 'SUBJECT'], ''));
                    } else {
                        $message->subject($params['subject']);
                    }
                    
                });
            
            return $result;
        }
    }
    
    
    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     * @return string
     */
    public function getEventAdminPrepareName(): string
    {
        return MailAdminPrepare::class;
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
                'name' => trans('config::forms.email.general.fields.name'),
                'key' => self::NAME,
                'domain' => true,
                'callback' => false,
                'link' => 'emails_item',
                'extra' => true,
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
                    BaseFilter::PLACEHOLDER => trans('config::forms.email.general.fields.name'),
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
     *
     */
    public function getDefaultProperties(): Collection
    {
        // TODO: добавит локализацию
        $result = [
            [
                BaseProperties::NAME => trans('config::properties.email.form_name'),
                BaseProperties::ALIAS => 'FROM_NAME',
                BaseProperties::VALUE => '',
                BaseProperties::SORT => 100,
                BaseProperties::TYPE => BaseProperties::TYPE_STRING,
                BaseProperties::DATA => json_encode([
                    'description' => trans('config::properties.email.form_name_description'),
                ]),
            ],
            [
                BaseProperties::NAME => trans('config::properties.email.from_address'),
                BaseProperties::ALIAS => 'FROM_ADDRESS',
                BaseProperties::VALUE => config('mail.from.address'),
                BaseProperties::SORT => 100,
                BaseProperties::TYPE => BaseProperties::TYPE_STRING,
                BaseProperties::DATA => json_encode([
                    'description' => trans('config::properties.email.address_description'),
                ]),
            ],
            [
                BaseProperties::NAME => trans('config::properties.email.subject'),
                BaseProperties::ALIAS => 'SUBJECT',
                BaseProperties::VALUE => '',
                BaseProperties::SORT => 100,
                BaseProperties::TYPE => BaseProperties::TYPE_STRING,
                BaseProperties::DATA => json_encode([
                    'description' => trans('config::properties.email.subject_description'),
                ]),
            ],
            [
                BaseProperties::NAME => trans('config::properties.email.title'),
                BaseProperties::ALIAS => 'TITLE',
                BaseProperties::VALUE => '',
                BaseProperties::SORT => 100,
                BaseProperties::TYPE => BaseProperties::TYPE_STRING,
                BaseProperties::DATA => json_encode([
                    'description' => trans('config::properties.email.title_description'),
                ]),
            ],
            [
                BaseProperties::NAME => trans('config::properties.email.title_header'),
                BaseProperties::ALIAS => 'TITLE_HEADER',
                BaseProperties::VALUE => '',
                BaseProperties::SORT => 100,
                BaseProperties::TYPE => BaseProperties::TYPE_STRING,
                BaseProperties::DATA => json_encode([
                    'description' => trans('config::properties.email.title_header_description'),
                ]),
            ],
        ];
        
        return collect($result);
    }
}
