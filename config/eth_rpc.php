<?php

return [
    /*
    | Host and port through which the node's RPC interface is reachable
    */

    // Enter 'localhost' if the node is running on your local machine
    'host' => env('ETH_RPC_HOST', 'http://localhost'),

    // The default port for most Ethereum nodes is 8545
    'port' => env('ETH_RPC_PORT', '8545')
];
