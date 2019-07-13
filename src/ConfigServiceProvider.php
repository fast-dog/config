<?php

namespace FastDog\Config;


use FastDog\Core\Models\ModuleManager;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ConfigServiceProvider extends LaravelServiceProvider
{
    const NAME = 'config';

    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->handleConfigs();
        $this->handleRoutes();
        $this->handleMigrations();
        $this->handleLang();

        /**
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make(ModuleManager::class);
        $moduleManager->pushModule(Config::MODULE_ID, (new Config())->getModuleInfo(true));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(ConfigEventServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }


    /**
     * Определение конфигурации по умолчанию
     */
    private function handleConfigs(): void
    {
        $configPath = __DIR__ . '/../config/config.php';
        $this->publishes([$configPath => config_path('config.php')]);

        $this->mergeConfigFrom($configPath, self::NAME);
    }

    /**
     * Миграции базы данных
     */
    private function handleMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations/');
    }


    /**
     * Определение маршрутов пакета
     */
    private function handleRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
    }


    /**
     * Определение локализации
     */
    private function handleLang(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
        $this->loadTranslationsFrom($path, self::NAME);
        $this->publishes([
            $path => resource_path('lang/vendor/fast_dog/' . self::NAME),
        ]);
    }
}