<?php

namespace IsGulkov\EthRPC\Tests;

use IsGulkov\EthRPC\Lib\EthRPC;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
// use GuzzleHttp\Exception\RequestException;


abstract class RpcMethodAssertions extends TestCase
{
    protected function assertUpstream($methodName, $params, $expectedParams=null)
    {
        if($expectedParams === null) {
            $expectedParams = $params;
        }

        $client = new Client(
            [
                'handler' => function($request) use ($methodName, $expectedParams) {
                    $requestBody = json_decode($request->getBody() . "");

                    $this->assertEquals($requestBody->method, $methodName);
                    $this->assertEquals($requestBody->params, $expectedParams);

                    return new Response(200, [], '{"result": ""}');
                }
            ]
        );

        $ethRpc = new EthRPC('127.0.0.1', '8545', "2.0", $client);

        call_user_func_array([$ethRpc, $methodName], $params);
        // $ethRpc->$methodName();
    }

    protected function assertDownstream($methodName, $params, $returnedResult, $expectedReturn=null)
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['result' => $returnedResult])),
            // new Response(202, ['Content-Length' => 0]),
            // new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $ethRpc = new EthRPC('127.0.0.1', '8545', "2.0", $client);

        if($expectedReturn === null) {
            $expectedReturn = $returnedResult;
        }

        $this->assertEquals(
            call_user_func_array([$ethRpc, $methodName], $params),
            $expectedReturn
        );
    }
}
