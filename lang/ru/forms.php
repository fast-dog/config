<?php
return [
    'domain' => [
        'new' => 'Новый домен',
        'general' => [
            'title' => 'Основная информация',
            'fields' => [
                'name' => 'Название',
                'url' => 'Ссылка',
                'code' => 'Код',
                'site_id' => 'Код сайта',
                'localization' => 'Локализация',
                'state' => 'Состояние',
                'created_at' => 'Дата создания',
                'updated_at' => 'Дата обновления',
            ],
        ],
        'extend' => [
            'title' => 'Дополнительные параметры',
        ],
    ],
    'components' => [
        'new' => 'Новый компонент',
        'general' => [
            'title' => 'Основная информация',
            'fields' => [
                'name' => 'Идетнификатор',
                'type' => 'Тип',
                'template' => 'Шаблон',
                'access' => 'Доступ',
                'state' => 'Состояние',
            ],
        ],
        'media' => [
            'title' => 'Медиа материалы',
        ],
        'extend' => [
            'title' => 'Дополнительные параметры',
        ],
    ],
    'email' => [
        'new' => 'Новое почтовое сообщение',
        'general' => [
            'title' => 'Основная информация',
            'fields' => [
                'name' => 'Название',
                'alias' => 'Псевдоним',
                'html' => 'HTML текст',
                'access' => 'Доступ',
                'state' => 'Состояние',
                'created_at' => 'Дата создания',
                'updated_at' => 'Дата обновления',

            ]
        ],
        'extend' => [
            'title' => 'Дополнительные параметры',
        ],
    ],
    'localization' => [
        'new' => 'Новое определение',
        'general' => [
            'title' => 'Основная информация',
            'fields' => [
                'code' => 'Код',
                'name' => 'Название',
                'value' => 'Значение',
                'access' => 'Доступ',
                'state' => 'Состояние',
            ]
        ]
    ]
];
