<h2 align="center">
    Ethereum RPC Package for Laravel
</h2>

>  **TODO**: put the badges back

## Introduction

This is a simple Laravel Service Provider providing for interacting with an Ethereum node through its [JSON RPC](https://github.com/ethereum/wiki/wiki/JSON-RPC) interface.

The description of [the package this is forked from](https://github.com/jcsofts/laravel-ethereum) said basically all the same, including the word "simple". Then I go in because something wasn't working and realize it could use a fair bit more of simplification.

Note that this package is mostly targeted at [Parity JSON API](https://wiki.parity.io/JSONRPC).

Installation
------------

>  **TODO**: change this if I finally put it up

You can't install install this library like usual with Composer

```bash
php composer.phar require isgulkov/ethrpc
```

because I'm too embarrased to publish it on packagist. So you have to intrude into your `composer.json` a bit.

Add `"isgulkov/ethrpc": "dev-master"` to `"require"`, but to make it work, also add this repository to `repositories`:

```json
{
    ...

    "repositories": [
        {
            "type": "vcs",
            "url": "<... this repo's GitHub URL ...>"
        }
    ],
	...
    "require": {
        ...
        "isgulkov/ethrpc": "dev-master"
        ...
    }
}
```

### Laravel 5.5+

The previous description said:

>  If you're using Laravel 5.5 or above, the package will automatically register the ~~`Ethereum`~~ `EthRPC` provider and facade.

Honestly, I have no idea. I've only ran 5.6 in my life.

### Laravel 5.4 and below

Here's the usual stuff about adding the provider and the facade alias to your `config/app.php`.

I personally don't know anything about neither 5.4 nor below, though.

### Using ~~Laravel-Ethereum~~ EthRPC with Lumen

Don't look at me! Read what [the other guy](https://github.com/jcsofts/laravel-ethereum/commit/91d4fb8d52f20586aef90fb507d7b67552290fe4) wrote. Maybe it still works.

Configuration
-------------

You can use `artisan vendor:publish` to copy the distribution configuration file to your app's config directory:

```bash
php artisan vendor:publish
```

Then update `config/eth_rpc.php` with your settings.

> **TODO**: list the keys?

Alternatively, you can set them in `.env` file, which the config file defaults to:

```dotenv
ETH_RPC_HOST=http://localhost
ETH_RPC_PORT=8545
```

## Usage


To use the Ethereum Client Library you can use the facade, or request the instance from the service container:

```php
    try {
        $ret = \EthRPC\EthRPC\Facade\EthRPC::eth_protocolVersion();
        print_r($ret);
    } catch (Exception $e){
        echo $e->getMessage();
    }
```

> **TODO**: this example ↑ has to be improved. Who writes like that?

Or

```php
$eth = app('EthRPC');

dd($eth::eth_protocolVersion());
```

>  **TODO**: provide a more elaborate explanation that just two examples, ffs — the library is 1 KB large

## Troubleshooting

#### I try to call a method I found in so-and-so wiki, but it says "method not supported"!

This probably has nothing to do with the library — it's the node. Which one are you using? Because command support differs greatly between them.

Also, on Parity, for example, most commands are disabled by default, and have to be manually enabled on startup.

So, look into your node. Feel free to post an issue, though.

## Credits

[That guy](https://github.com/jcsofts), the owner of [this repo](https://github.com/jcsofts/laravel-ethereum) that I forked this from. Maybe it's a half-baked fork of some better thing, but I couldn't find anything close to it and serioursly doubt that.

