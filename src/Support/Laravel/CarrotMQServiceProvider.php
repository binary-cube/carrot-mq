<?php

namespace BinaryCube\CarrotMQ\Support\Laravel;

use Psr\Log\LoggerInterface;
use BinaryCube\CarrotMQ\CarrotMQ;
use Illuminate\Foundation\Application;
use BinaryCube\CarrotMQ\Support\Laravel\Console\Commands\ListCommand;
use BinaryCube\CarrotMQ\Support\Laravel\Console\Commands\SetupCommand;
use BinaryCube\CarrotMQ\Support\Laravel\Console\Commands\PurgeCommand;
use BinaryCube\CarrotMQ\Support\Laravel\Console\Commands\ConsumerCommand;
use BinaryCube\CarrotMQ\Support\Laravel\Console\Commands\DeleteAllCommand;
use BinaryCube\CarrotMQ\Support\Laravel\Console\Commands\PublisherCommand;

/**
 * Class CarrotMQServiceProvider
 */
class CarrotMQServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->publishConfig();
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->prepareApp();
        $this->prepareCommands();
    }

    /**
     * @return void
     */
    protected function publishConfig()
    {
        $this
            ->publishes(
                [
                    $this->getConfigFile() => config_path('carrot_mq.php'),
                ]
            );
    }

    /**
     * @return void
     */
    protected function prepareApp()
    {
        $config = config('carrot_mq', []);

        if (! \is_array($config)) {
            throw new \RuntimeException(
                'Invalid configuration provided for CarrotMQ-Laravel!'
            );
        }

        $this->app->bind(
            'carrot.mq',
            function (Application $app) {
                return app(CarrotMQ::class);
            }
        );

        $this->app->singleton(
            CarrotMQ::class,
            function (Application $app, $arguments) use ($config) {
                $logger = $app->make(LoggerInterface::class);
                $carrot = new CarrotMQ($config, $logger);

                return $carrot;
            }
        );
    }

    /**
     * @return void
     */
    protected function prepareCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands(
            [
                ListCommand::class,
                SetupCommand::class,
                ConsumerCommand::class,
                DeleteAllCommand::class,
                PurgeCommand::class,
                PublisherCommand::class,
            ]
        );
    }

    /**
     * @return string
     */
    protected function getConfigFile()
    {
        return __DIR__ . '/config/carrot_mq.php';
    }

}
