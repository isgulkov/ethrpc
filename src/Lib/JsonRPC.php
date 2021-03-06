<?php

namespace IsGulkov\EthRPC\Lib;

use GuzzleHttp;

class JsonRPC
{
    protected $baseUri, $version;
    protected $id = 0;
    private $client;
    private $timeout;

    function getTimeout()
    {
        return $this->timeout;
    }

    function __construct($host, $port, $options=[])
    {
        $baseUri = $host . ":" . $port;

        $this->timeout = isset($options['timeout']) ? $options['timeout'] : 1;
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
        catch(GuzzleHttp\Exception\ConnectException $e) {
            if(stripos($e->getMessage(), "cURL error 28") !== false) {
                $groups = [];

                if(preg_match('/(\d+) milliseconds/', $e->getMessage(), $groups)) {
                    throw new HTTPTimeoutException(intval($groups[1]));
                }
                else {
                    throw new HTTPTimeoutException();
                }

                // TODO: cURL error 7 -- connection refused
            }
            else {
                throw $e;
            }
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            // TODO: rethrow another exception?
            throw $e;
        }
    }

    function format_response($response)
    {
        return @json_decode($response);
    }
}
