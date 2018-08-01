<?php

namespace IsGulkov\EthRPC\Lib;

use GuzzleHttp;

class JsonRPC
{
    protected $baseUri, $version;
    protected $id = 0;
    private $client;
    private $timeout;

    function __construct($host, $port, $options=[])
    {
        $baseUri = $host . ":" . $port;

        $this->timeout = isset($options['timeout']) ? $options['timeout'] : 0;
        $this->version = isset($options['version']) ? $options['version'] : "2.0";

        $this->client = isset(
            $options['client']
        ) ? $options['client'] : new GuzzleHttp\Client(
            [ 'base_uri' => $baseUri ]
        );
    }

    function request($method, $params=array())
    {
        $data = array();
        $data['jsonrpc'] = $this->version;
        $data['id'] = $this->id++;
        $data['method'] = $method;
        $data['params'] = $params;

        try {
            $res = $this->client->request("POST", '', [
                'headers'  => [
                    'content-type' => 'application/json'
                ],
                'json' => $data,
                'timeout' => $this->timeout
            ]);

            $formatted = json_decode($res->getBody()->getContents());

            if(isset($formatted->error)) {
                throw new RPCException(
                    $formatted->error->message,
                    $formatted->error->code
                );
            }
            else {
                return $formatted;
            }
        }
        catch (ClientException $e) {
            throw $e;
        }
    }

    function format_response($response)
    {
        return @json_decode($response);
    }
}
