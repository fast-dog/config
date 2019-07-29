<?php

namespace FastDog\Config;


use FastDog\Config\Policies\DomainPolicy;
use FastDog\Config\Policies\EmailsPolicy;
use FastDog\Config\Policies\HelpPolicy;
use FastDog\Config\Policies\TranslatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Class AuthServiceProvider
 *
 * @package FastDog\Config
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Сопоставление политик для приложения.
     *
     * @var array
     */
    protected $policies = [
        \FastDog\Core\Models\Domain::class => DomainPolicy::class,
        \FastDog\Config\Models\Emails::class => EmailsPolicy::class,
        \FastDog\Config\Models\Translate::class => TranslatePolicy::class,
        \FastDog\Config\Models\ConfigHelp::class => HelpPolicy::class,

    ];

    /**
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
