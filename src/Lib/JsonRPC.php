<?php

namespace IsGulkov\EthRPC\Lib;

use GuzzleHttp;

class JsonRPC
{
    protected $baseUri, $version;
    protected $id = 0;
    private $client;

    function __construct($host, $port, $version="2.0", GuzzleHttp\Client $client=null)
    {
        $baseUri = $host . ":" . $port;
        $this->version = $version;

        if($client === null) {
            $client = new GuzzleHttp\Client([
                'base_uri' => $baseUri
            ]);
        }

        $this->client = $client;
    }

    function request($method, $params=array())
    {
        $data = array();
        $data['jsonrpc'] = $this->version;
        $data['id'] = $this->id++;
        $data['method'] = $method;
        $data['params'] = $params;

        try {
            $res = $this->client->request("POST",'', [
                'headers'  => ['content-type' => 'application/json'],
                'json' => $data,
                'timeout' => 1
                // TODO: let configure
            ]);
            $formatted=json_decode($res->getBody()->getContents());

            //print_r($formatted);
            if(isset($formatted->error))
            {
                throw new RPCException($formatted->error->message, $formatted->error->code);
            }
            else
            {
                return $formatted;
            }
        } catch (ClientException $e) {
            throw $e;
        }
    }

    function format_response($response)
    {
        return @json_decode($response);
    }
}
