<?php

namespace FastDog\Config;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ConfigEventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'FastDog\Config\Events\DomainsItemsAdminPrepare' => [
            'FastDog\Config\Listeners\DomainsItemsAdminPrepare',
        ],
        'FastDog\Core\Events\DomainsItemAdminPrepare' => [
            'FastDog\Core\Listeners\AdminItemPrepare',// <-- Поля даты обновления и т.д.
            'FastDog\Config\Listeners\DomainsItemAdminPrepare',
            'FastDog\Config\Listeners\DomainsItemSetEditForm',// <-- Форма редактирования
        ],
        'FastDog\Config\Events\Components\ComponentItemsAdminPrepare' => [
            'FastDog\Config\Listeners\Components\ComponentItemsAdminPrepare',
        ],
        'FastDog\Config\Events\Components\ComponentItemAdminPrepare' => [
            'FastDog\Core\Listeners\AdminItemPrepare',// <-- Поля даты обновления и т.д.
            'FastDog\Config\Listeners\Components\ComponentItemAdminPrepare',
            'FastDog\Config\Listeners\Components\ComponentItemSetEditForm',// <-- Форма редактирования
        ],
        'FastDog\Config\Events\Components\ComponentItemBeforeSave' => [
            'FastDog\Core\Listeners\ModelBeforeSave',// <-- Упаковка дополнительных парамтеров в json поле data
            'FastDog\Config\Listeners\Components\ComponentItemBeforeSave',
        ],
        'FastDog\Config\Events\Components\ComponentItemAfterSave' => [
            'FastDog\Config\Listeners\Components\ComponentItemAfterSave',
        ],

        'FastDog\Config\Events\MailAdminPrepare' => [
            'FastDog\Core\Listeners\AdminItemPrepare',// <-- Поля даты обновления и т.д.
            'FastDog\Config\Listeners\MailAdminPrepare',
            'FastDog\Config\Listeners\EmailsItemSetEditForm',// <-- Форма редактирования
        ],
        'FastDog\Config\Events\ServiceAdminPrepare' => [
            'FastDog\Config\Listeners\ServiceAdminPrepare',
        ],
        'FastDog\Config\Events\HelpAdminPrepare' => [
            'FastDog\Core\Listeners\AdminItemPrepare',// <-- Поля даты обновления и т.д.
            'FastDog\Config\Listeners\HelpAdminPrepare',
            'FastDog\Config\Listeners\HelpItemSetEditForm',// <-- Форма редактирования
        ],
        'FastDog\Config\Events\Localization\LocalizationAdminPrepare' => [
            'FastDog\Core\Listeners\AdminItemPrepare',// <-- Поля даты обновления и т.д.
            'FastDog\Config\Listeners\Localization\LocalizationAdminPrepare',
            'FastDog\Config\Listeners\Localization\LocalizationSetEditForm',// <-- Форма редактирования
        ],
        'FastDog\Core\Events\GetComponentType' => [

        ],
    ];


    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();


        //
    }

    public function register()
    {
        //
    }
}