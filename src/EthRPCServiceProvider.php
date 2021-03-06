<?php

namespace IsGulkov\EthRPC;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use IsGulkov\EthRPC\Lib\EthRPC;

class EthRPCServiceProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $dist = __DIR__.'/../config/eth_rpc.php';

        if (function_exists('config_path')) {
            // Publishes config File.
            $this->publishes([
                $dist => config_path('eth_rpc.php'),
            ]);
        }

        $this->mergeConfigFrom($dist, 'eth_rpc');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(EthRPC::class, function ($app) {
            return $this->createInstance($app['config']);
        });

        // TODO: add Laravel dependency to package.json

        $this->commands([
            Commands\EthRpcNodeDiag::class
        ]);

        // $this->commands('command.eternaltree.install');

        // $this->getOutput()->setDecorated( true );

    }

    public function provides()
    {
        return [EthRPC::class];
    }

    protected function createInstance(Repository $config)
    {
        // Check for ethereum config file.
        if (! $this->hasConfigSection()) {
            $this->raiseRunTimeException("Missing Ethereum RPC configuration section.");
        }

        // Check for username.
        if ($this->configHasNo('host')) {
            $this->raiseRunTimeException("Missing Ethereum RPC config key: 'host'.");
        }

        // check the password
        if ($this->configHasNo('port')) {
            $this->raiseRunTimeException("Missing Ethereum RPC config key: 'port'.");
        }

        return new EthRPC(
            $config->get('eth_rpc.host'),
            $config->get('eth_rpc.port'),
            $this->configHas('timeout') ? [
                'timeout' => $config->get('eth_rpc.timeout')
            ] : []
        );
    }

    /**
     * Checks if has global ethereum configuration section.
     *
     * @return bool
     */
    protected function hasConfigSection()
    {
        return $this->app->make(Repository::class)->has('eth_rpc');
    }

    /**
     * Checks if Nexmo config does not
     * have a value for the given key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function configHasNo($key)
    {
        return ! $this->configHas($key);
    }

    /**
     * Checks if ethereum config has value for the
     * given key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function configHas($key)
    {
        /** @var Config $config */
        $config = $this->app->make(Repository::class);
        // Check for ethereum config file.
        if (! $config->has('eth_rpc')) {
            return false;
        }
        return
            $config->has('eth_rpc.'.$key) &&
            ! is_null($config->get('eth_rpc.'.$key)) &&
            ! empty($config->get('eth_rpc.'.$key));
    }

    /**
     * Raises Runtime exception.
     *
     * @param string $message
     *
     * @throws \RuntimeException
     */
    protected function raiseRunTimeException($message)
    {
        throw new \RuntimeException($message);
    }
}
