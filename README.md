<h2 align="center">
    Ethereum RPC Package for Laravel
</h2>

>  **TODO**: put the badges back

## Introduction

This is a simple Laravel Service Provider providing for interacting with an Ethereum node through its [JSON RPC](https://github.com/ethereum/wiki/wiki/JSON-RPC) interface.

The description of [the package this is forked from](https://github.com/jcsofts/laravel-ethereum) said basically all the same, including the word "simple". Then I go in because something wasn't working and realize it could use a fair bit more of simplification.

Note that this package is mostly targeted at [Parity](https://wiki.parity.io/JSONRPC) — I haven't used any of the other nodes much.

### How it works

JSON RPC itself is so simple that this library has to do jack shit. Most of the time, you call a method with some params:

```php
$eth->eth_somethingOrOther("this", "that", "other");
```

and the probram literally just forwards this to the node in an HTTP `POST`'s body':

```js
{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "eth_somethingOrOther",
    "params": ["this", "that", "other"]
}
```

The node returns you either the result:

```js
{
	"id": 1,
	"jsonrpc": "2.0",
    "result": "Donald Trump"
}
```

or error response:

```js
{
    "jsonrpc": "2.0",
    "id": 1,
    "error": {
        "code": -32600,
        "message": "Invalid request"
    }
}
```

This is literally all the library does — pass simple JSON through, sometimes throwing an exception. No parsing of binary data packets, no complex logic, no encyption. Just a small convenience.

**There are some caveats concerning numbers, though.** Might do that in the future.

Installation
------------

>  **TODO**: change this if I finally put it up

You can't install install this library like usual with Composer

```bash
php composer.phar require isgulkov/ethrpc
```

because I'm too embarrased to publish it on packagist. So you have to intrude into your `composer.json` a bit.

Add `"isgulkov/ethrpc": "dev-master"` to `"require"`, but to make it work, also add this repository to `repositories`:

```js
{
    // ...

    "repositories": [
        {
            "type": "vcs",
            "url": "<... this repo's GitHub URL ...>"
        }
    ],

	// ...

    "require": {
        // ...
        "isgulkov/ethrpc": "dev-master"
        // ...
    }

    // ...
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

Alternatively, you can set them in `.env` file, which the default config file uses:

```dotenv
ETH_RPC_HOST=http://localhost
ETH_RPC_PORT=8545
ETH_RPC_TIMEOUT=1
```

after which, it falls back to the defaults (see above).

Note: `timeout`  is the time (in seconds) the HTTP client is going to wait for a node's response. Set to `0` to wait indefinitely. Be careful, though — if the node is reluctant to respond, which is not unheard of, your application will block for that much time.

The default of 1 second is vastly more than enough for a local node.

> **TODO**: either remove the default values from `env()` calls, or remove all "no config" checks — one or the other!

## Usage


To use the Ethereum Client Library you can use the facade:

```php
use IsGulkov\EthRPC\Facade\EthRPC;

class Mocha {
    public function __construct(EthRPC $ethRPC) {
        $this->eth = $ethRPC;
    }

    function getGlobalSionistGovernmentBalance() {
        return $this->eth::eth_getBalance("0x0000000000000000000000000000000000000000");
    }

    // ...
}
```

or request the instance from the service container:

```php
$eth = app('EthRPC');

dd($eth::eth_protocolVersion());
```

Thus, you can call any methods supported *by your node*:

- [JSON RPC](https://wiki.parity.io/JSONRPC) — Parity wiki;
- [JSON RPC](https://github.com/ethereum/wiki/wiki/JSON-RPC#json-rpc-endpoint) — official Ethereum wiki;
- [Implemented (RPC) methods](https://github.com/trufflesuite/ganache-cli#implemented-methods) — `README.md` for Ganache CLI.

Encoding and decoding hex data (such as hexadeciaml integers or byte strings) is currently completely on the user.

I'm planning to address this as soon as the library is half-usable as it is; though it doesn't seem like too good of an idea to me.

## Credits

[This guy](https://github.com/jcsofts), the owner of [this repo](https://github.com/jcsofts/laravel-ethereum) that I forked it from. Maybe it's a half-baked fork of some better thing, but I couldn't find anything close to it and serioursly doubt that.

