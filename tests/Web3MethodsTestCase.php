<?php

namespace IsGulkov\EthRPC\Tests;


class Web3MethodsTestCase extends RpcMethodAssertions
{
    public function test_web3_clientVersion_U()
    {
        $this->assertUpstream('web3_clientVersion', []);
    }

    public function test_web3_clientVersion_D()
    {
        $this->assertDownstream('web3_clientVersion', [], "Parity//v1.11.8");
    }

    public function test_web3_sha3_U()
    {
        $this->assertUpstream('web3_sha3', ['hello world']);
    }

    public function test_web3_sha3_D()
    {
        $this->assertDownstream('web3_sha3', ['hello world'], "abc");
    }
}
