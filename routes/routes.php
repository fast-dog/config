<?php

use FastDog\Core\Models\DomainManager;
use Illuminate\Http\Request;

Route::group([
    'prefix' => config('core.admin_path', 'admin'),
    'middleware' => ['web', FastDog\Admin\Http\Middleware\Admin::class],
], function () {

    $ctrl = '\FastDog\Config\Http\Controllers\ConfigController';

    $baseParameters = [];

    // Домены - список
    \Route::post('/config/domains', array_replace_recursive($baseParameters, [
        'uses' => '\FastDog\Config\Http\Controllers\Domain\DomainTableController@list',

    ]));
    // Домены - просмотр параметров
    \Route::get('/config/domains/{id}', array_replace_recursive($baseParameters, [
        'uses' => '\FastDog\Config\Http\Controllers\Domain\DomainFormController@getEditItem',

    ]));
    // Домены - добавление нового домена
    \Route::post('/config/domain/add', array_replace_recursive($baseParameters, [
        'uses' => '\FastDog\Config\Http\Controllers\Domain\DomainFormController@postUpdate',

    ]));
    // Домены - обновление параметров домена
    \Route::post('/config/domain/save', array_replace_recursive($baseParameters, [
        'uses' => '\FastDog\Config\Http\Controllers\Domain\DomainFormController@postUpdate',

    ]));
    // Домены - сохранение параметров из списка
    \Route::post('/config/domains/self-update', array_replace_recursive($baseParameters, [
        'uses' => '\FastDog\Config\Http\Controllers\Domain\DomainFormController@postDomainsUpdate',

    ]));

    // Компоненты таблица
    $ctrl = '\FastDog\Config\Http\Controllers\Components\ComponentsTableController';

    // Компоненты - список компонентов
    \Route::post('/config/components', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@list',

    ]));

    // Компоненты формы
    $ctrl = '\FastDog\Config\Http\Controllers\Components\ComponentsFormController';

    // Компоненты - информация о компоненте (публикуемом модуле)
    \Route::get('/config/components/{id}', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@getEditItem',

    ]))->where('id', '[1-90]+');

    // Компоненты - обновление параметров компонента из формы
    \Route::post('/config/components/save', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postUpdate',

    ]));

    // Компоненты - добавление нового компонента
    \Route::post('/config/components/add', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postUpdate',

    ]));

    // Компоненты - сохранение основных параметров
    \Route::post('/config/components/self-update', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postModelUpdateFromTable',

    ]));
    // Компоненты - копирование
    \Route::post('/config/components/replicate', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postReplicate',

    ]));
    // Почтовые события таблица
    $ctrl = '\FastDog\Config\Http\Controllers\Emails\EmailsTableController';

    //список почтовых событий
    \Route::post('/config/emails', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@list',
    ]));

    // Emails форма
    $ctrl = '\FastDog\Config\Http\Controllers\Emails\EmailsFormController';

    //обновление параметров почтовых событий
    \Route::post('/config/emails/update', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postEmailsUpdate',

    ]));

    //запрос данных почтовых событий
    \Route::get('/config/emails/{id}', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@getEditItem',

    ]));

    //добавление\обновление почтовых событий
    \Route::post('/config/emails/save', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postUpdate',

    ]));


    // АПИ
    $ctrl = '\FastDog\Config\Http\Controllers\ApiController';

    //страница помощи
    \Route::get('/config/help-page/', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@getHelpPage',

    ]));

    // страница настройки форм
    \Route::get('/config/forms/{id}', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@getForm',
    ]))->where('id', '[1-90]+');

    // страница настройки форм
    \Route::post('/config/forms/{id}', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postForm',
    ]))->where('id', '[1-90]+');

    // Добавление\Сохранение дополнительного параметра
    \Route::post('/config/save-property', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postSaveProperty',
    ]));

    // Удаление значения дополнительного параметра
    \Route::post('/config/delete-select-value', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postDeleteSelectValue',
    ]));

    // Добавление значения дополнительного параметра
    \Route::post('/config/add-select-value', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postAddSelectValue',
    ]));
//    //страница настроек
//    \Route::get('/config/admin-info/', array_replace_recursive($baseParameters, [
//        'uses' => $ctrl . '@getAdminInfo',
//
//    ]));
//
//    //изменение разрешений для модуля
//    \Route::post('/config/access', array_replace_recursive($baseParameters, [
//        'uses' => $ctrl . '@postAccess',
//
//    ]));

//    //выполнение команд для отдельного модуля (переустановить\обновить ACL и т.д.)
//    \Route::post('/config/modules/cmd', array_replace_recursive($baseParameters, [
//        'uses' => $ctrl . '@postModuleCmd',
//
//    ]));

    /*
     * Локализация - таблица
     */
    $ctrl = '\FastDog\Config\Http\Controllers\Localization\LocalizationTableController';
    //список локализации
    \Route::post('/config/localization', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@list',

    ]));

    /*
     * Локализация - форма редактирования
     */
    $ctrl = '\FastDog\Config\Http\Controllers\Localization\LocalizationFormController';

    //термин локализации
    \Route::get('/config/localization/{id}', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@getEditItem',

    ]));

    //добавление\обновление термина локализации
    \Route::post('/config/localization/save', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postUpdate',
    ]));

    //добавление\обновление термина локализации
    \Route::post('/config/localization/update', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postLocalizationUpdate',
    ]));


//
//        //список личных сообщений
//        \Route::post('/config/system-messages', array_replace_recursive($baseParameters, [
//            'uses' => $ctrl . '@postMessages',
//            'can' => 'api.' . $this->aclName,
//        ]));
//        //запрос данных личных сообщений
//        \Route::get('/config/system-message/{id}', array_replace_recursive($baseParameters, [
//            'uses' => $ctrl . '@getMessage',
//            'can' => 'api.' . $this->aclName,
//        ]));
//        //добавление\обновление личных сообщений
//        \Route::post('/config/system-messages/save', array_replace_recursive($baseParameters, [
//            'uses' => $ctrl . '@postMessageSave',
//            'can' => 'api.' . $this->aclName,
//        ]));
//
//        //добавление\обновление личных сообщений
//        \Route::post('/config/system-messages/self-update', array_replace_recursive($baseParameters, [
//            'uses' => $ctrl . '@postMessageSelfUpdate',
//            'can' => 'api.' . $this->aclName,
//        ]));

//

    /*
     * Помощь администраторам - таблица
     */
    $ctrl = '\FastDog\Config\Http\Controllers\Help\HelpTableController';

    //список страниц помощи
    \Route::post('/config/helps', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@list',

    ]));
    //обновление страницы помощи
    \Route::post('/config/helps/update', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postHelpUpdate',

    ]));

    /*
     * Помощь администраторам - форма
     */
    $ctrl = '\FastDog\Config\Http\Controllers\Help\HelpFormController';

    // добавление\обновление страницы помощи
    \Route::get('/config/help/{id}', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@getEditItem',

    ]));
    // добавление\обновление страницы помощи
    \Route::post('/config/help/save', array_replace_recursive($baseParameters, [
        'uses' => $ctrl . '@postHelpSave',

    ]));

    /*
     * Очистка кэша, удаление скомпилированных шаблонов
     */
    \Route::post('/config/clear-cache', function (Request $request) {
        switch ($request->input('type')) {
            case 'views':
                \Artisan::call('view:clear');
                break;
        }
        $isRedis = config('cache.default') == 'redis';
        if ($isRedis) {
            switch ($request->input('type')) {
                case 'catalog':
                    \Cache::tags(['catalog'])->flush();
                    break;
                case 'content':
                    \Cache::tags(['content'])->flush();
                    break;
                case 'event':
                    \Cache::tags(['event'])->flush();
                    break;
                case 'views':
                    \Artisan::call('template-clear');
                    break;
                default:
                    \Artisan::call('cache:clear');
                    \Cache::tags(['core'])->flush();
                    break;
            }
        } else {
            \Artisan::call('cache:clear');
        }

    });
});
