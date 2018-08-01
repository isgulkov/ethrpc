<?php

namespace IsGulkov\EthRPC\Tests;


class NetMethodsTestCase extends RpcMethodAssertions
{
    public function test_net_listening_U()
    {
        $this->assertUpstream('net_listening', []);
    }

    public function test_net_listening_D()
    {
        $this->assertDownstream('net_listening', [], true);
    }

    public function test_net_peerCount_U()
    {
        $this->assertUpstream('net_peerCount', []);
    }

    public function test_net_peerCount_D()
    {
        $this->assertDownstream('net_peerCount', [], "0x2");
    }

    public function test_web3_sha3_U()
    {
        $this->assertUpstream('net_version', []);
    }

    public function test_web3_sha3_D()
    {
        $this->assertDownstream('net_version', [], "8213");
    }
}
